<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['author:read']],
    denormalizationContext: ['groups' => ['author:write']]
)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['author:read', 'book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['author:read', 'author:write', 'book:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['author:read', 'author:write'])]
    private ?string $biography = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['author:read', 'author:write'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['author:read', 'author:write'])]
    private ?string $nationality = null;

    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'authors')]
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addAuthor($this);
        }
        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeAuthor($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
