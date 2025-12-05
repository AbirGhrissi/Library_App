<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['book:read']],
    denormalizationContext: ['groups' => ['book:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'isbn' => 'exact', 'authors.name' => 'partial', 'categories.name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['title', 'publicationDate', 'price'])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?\DateTimeInterface $publicationDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $price = null;

    #[ORM\Column]
    #[Groups(['book:read', 'book:write'])]
    private ?int $stockQuantity = 0;

    #[ORM\Column]
    #[Groups(['book:read', 'book:write'])]
    private ?int $borrowableQuantity = 0;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $coverImage = null;

    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'books')]
    #[ORM\JoinTable(name: 'book_author')]
    #[Groups(['book:read', 'book:write'])]
    private Collection $authors;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[Groups(['book:read', 'book:write'])]
    private ?Publisher $publisher = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books')]
    #[ORM\JoinTable(name: 'book_category')]
    #[Groups(['book:read', 'book:write'])]
    private Collection $categories;

    #[ORM\OneToMany(targetEntity: Borrowing::class, mappedBy: 'book', orphanRemoval: true)]
    private Collection $borrowings;

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'book', orphanRemoval: true)]
    private Collection $purchases;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->borrowings = new ArrayCollection();
        $this->purchases = new ArrayCollection();
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

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate): static
    {
        $this->publicationDate = $publicationDate;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;
        return $this;
    }

    public function getBorrowableQuantity(): ?int
    {
        return $this->borrowableQuantity;
    }

    public function setBorrowableQuantity(int $borrowableQuantity): static
    {
        $this->borrowableQuantity = $borrowableQuantity;
        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }
        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        $this->authors->removeElement($author);
        return $this;
    }

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher): static
    {
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);
        return $this;
    }

    public function getBorrowings(): Collection
    {
        return $this->borrowings;
    }

    public function addBorrowing(Borrowing $borrowing): static
    {
        if (!$this->borrowings->contains($borrowing)) {
            $this->borrowings->add($borrowing);
            $borrowing->setBook($this);
        }
        return $this;
    }

    public function removeBorrowing(Borrowing $borrowing): static
    {
        if ($this->borrowings->removeElement($borrowing)) {
            if ($borrowing->getBook() === $this) {
                $borrowing->setBook(null);
            }
        }
        return $this;
    }

    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setBook($this);
        }
        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            if ($purchase->getBook() === $this) {
                $purchase->setBook(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
