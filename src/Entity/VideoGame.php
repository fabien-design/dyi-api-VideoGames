<?php

namespace App\Entity;

use App\Repository\VideoGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoGameRepository::class)]
class VideoGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:detail', 'editor:detail', 'videoGame:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category:detail', 'editor:detail', 'videoGame:read', 'videoGame:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'The title must be at least {{ limit }} characters long',
        maxMessage: 'The title cannot be longer than {{ limit }} characters'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['category:detail', 'editor:detail', 'videoGame:read', 'videoGame:write'])]
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(length: 512)]
    #[Groups(['category:detail', 'editor:detail', 'videoGame:read', 'videoGame:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 10,
        max: 512,
        minMessage: 'The description must be at least {{ limit }} characters long',
        maxMessage: 'The description cannot be longer than {{ limit }} characters'
    )]
    private ?string $description = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'videoGames')]
    #[Groups(['videoGame:detail'])]
    private Collection $category;

    #[ORM\ManyToOne(inversedBy: 'videoGames')]
    #[Groups(['videoGame:detail'])]
    private ?Editor $editor = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): static
    {
        $this->editor = $editor;

        return $this;
    }
}
