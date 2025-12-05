<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PublisherRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['publisher:read']],
    denormalizationContext: ['groups' => ['publisher:write']]
)]
class Publisher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['publisher:read', 'book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['publisher:read', 'publisher:write', 'book:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['publisher:read', 'publisher:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['publisher:read', 'publisher:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['publisher:read', 'publisher:write'])]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'publisher')]
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
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
            $book->setPublisher($this);
        }
        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            if ($book->getPublisher() === $this) {
                $book->setPublisher(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
