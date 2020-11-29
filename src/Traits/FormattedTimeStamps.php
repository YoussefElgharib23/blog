<?php


namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

Trait FormattedTimeStamps
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
     * @ORM\Column(type="string", length=255)
     * @Groups("post:ajax")
     * @Groups("category:get")
     */
    private $formattedCreatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:ajax")
     * @Groups("category:get")
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

    public function getFormattedCreatedAt(): ?string
    {
        return $this->formattedCreatedAt;
    }

    public function setFormattedCreatedAt(string $formattedCreatedAt): self
    {
        $this->formattedCreatedAt = $formattedCreatedAt;
        return $this;
    }

    public function getFormattedUpdatedAt(): ?string
    {
        return $this->formattedUpdatedAt;
    }

    public function setFormattedUpdatedAt(string $formattedUpdatedAt): self
    {
        $this->formattedUpdatedAt = $formattedUpdatedAt;
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

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setFormattedTimeStamps()
    {
        if ( $this->getFormattedCreatedAt() === null ) $this->setFormattedCreatedAt(date_format($this->getCreatedAt(),'M d, Y'));
        $this->setFormattedUpdatedAt(date_format($this->getUpdatedAt(), 'M d, Y'));
    }
}