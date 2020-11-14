<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("ajax:notifications")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("ajax:notifications")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("ajax:notifications")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $IsViewed;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="notifications")
     */
    private $Post;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateTimeStamps()
    {
        if ( $this->getCreatedAt() === NULL ) $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getIsViewed(): ?bool
    {
        return $this->IsViewed;
    }

    public function setIsViewed(bool $IsViewed): self
    {
        $this->IsViewed = $IsViewed;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->Post;
    }

    public function setPost(?Post $Post): self
    {
        $this->Post = $Post;

        return $this;
    }
}
