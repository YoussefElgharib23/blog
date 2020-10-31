<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimeStamps
{

    /**
     * @ORM\Column(type="datetime", options={"defaults": "CURRENT_TIMESTAMP"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $formattedCreatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $formattedUpdatedAt;

    public function setFormattedCreatedAt()
    {
        $formattedCreatedAt = date_format($this->getCreatedAt(), 'M d, Y');

        return $this;
    }

    public function setFormattedUpdatedAt()
    {
        $formattedUpdatedAt = date_format($this->getUpdatedAt(), 'M d, Y');

        return $this;
    }

    public function getFormattedCreatedAt()
    {
        return $this->formattedCreatedAt;
    }

    public function getFormattedUpdatedAt()
    {
        return $this->formattedUpdatedAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateTimeStamps()
    {
        if ( $this->getCreatedAt() === NULL ) $this->setCreatedAt(new \DateTimeImmutable());
        $this->setUpdatedAt(new \DateTimeImmutable());
    }
}