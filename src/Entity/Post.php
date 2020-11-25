<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Traits\TimeStamps;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ORM\Table(name="posts")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"title"})
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:ajax")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:ajax")
     * @Groups("ajax:notifications")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups("post:ajax")
     */
    private $description;

    /**
     * @ORM\Column(type="integer", options={"defaults":0})
     * @Groups("post:ajax")
     */
    private $views;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:ajax")
     */
    private $imageLink;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:ajax")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="post", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Like::class, mappedBy="post")
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="Post")
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity=Dislike::class, mappedBy="Post")
     */
    private $dislikes;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:ajax")
     */
    private $formattedCreatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:ajax")
     */
    private $formattedUpdatedAt;

    /**
     * @ORM\Column(type="datetime", options={"defaults": "CURRENT_TIMESTAMP"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updated_at;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    /**
     * RETURN THE TITLE OF THE POST AS SLUG
     *
     * @return string
     */
    public function getSlug(): string
    {
        return ( new Slugify() )->slugify($this->getTitle());
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function incrementViews()
    {
        $this->setViews($this->getViews() + 1);
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(?string $imageLink): self
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setPost($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isLikedByUser(User $user): bool
    {
        foreach ( $this->getLikes() as $like ) if ( $like->getUser() === $user) return true;
        return false;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setPost($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPost() === $this) {
                $notification->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Dislike[]
     */
    public function getDislikes(): Collection
    {
        return $this->dislikes;
    }

    public function addDislike(Dislike $dislike): self
    {
        if (!$this->dislikes->contains($dislike)) {
            $this->dislikes[] = $dislike;
            $dislike->setPost($this);
        }

        return $this;
    }

    public function removeDislike(Dislike $dislike): self
    {
        if ($this->dislikes->removeElement($dislike)) {
            // set the owning side to null (unless already changed)
            if ($dislike->getPost() === $this) {
                $dislike->setPost(null);
            }
        }

        return $this;
    }

    /**
     * CHECK IF THE USER IS DISLIKE THE CURRENT POST
     *
     * @param User $user
     * @return bool
     */
    public function isDislikedByUser(User $user)
    {
        foreach ($this->getDislikes() as $dislike) if ( $dislike->getUser() === $user) return true;
        return false;
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
