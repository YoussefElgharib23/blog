<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Traits\TimeStamps;
use Cocur\Slugify\Slugify;
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
    use TimeStamps;
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
}
