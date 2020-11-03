<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait TimeStamps
{

    /**
     * @ORM\Column(type="datetime", options={"defaults": "CURRENT_TIMESTAMP"})
     * @Groups("category:search")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     * @Groups("category:search")
     */
    private $updated_at;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups("category:search")
     */
    private $formattedCreatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups("category:search")
     */
    private $formattedUpdatedAt;

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
        $this->setFormattedCreatedAt(date_format($this->getCreatedAt(), 'M d, Y'));
        $this->setFormattedUpdatedAt(date_format($this->getUpdatedAt(), 'M d, Y'));
    }

    /**
     * @return string
     */
    public function getFormattedCreatedAt(): ?string
    {
        return $this->formattedCreatedAt;
    }

    /**
     * @param string $formattedCreatedAt
     */
    public function setFormattedCreatedAt(string $formattedCreatedAt): void
    {
        $this->formattedCreatedAt = $formattedCreatedAt;
    }

    /**
     * @return string
     */
    public function getFormattedUpdatedAt(): ?string
    {
        return $this->formattedUpdatedAt;
    }

    /**
     * @param string $formattedUpdatedAt
     */
    public function setFormattedUpdatedAt(string $formattedUpdatedAt): void
    {
        $this->formattedUpdatedAt = $formattedUpdatedAt;
    }
}