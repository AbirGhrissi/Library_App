<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BorrowingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BorrowingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['borrowing:read']],
    denormalizationContext: ['groups' => ['borrowing:write']],
    security: "is_granted('ROLE_USER')"
)]
class Borrowing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['borrowing:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'borrowings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['borrowing:read', 'borrowing:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'borrowings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['borrowing:read', 'borrowing:write'])]
    private ?Book $book = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['borrowing:read'])]
    private ?\DateTimeInterface $borrowedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['borrowing:read', 'borrowing:write'])]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['borrowing:read', 'borrowing:write'])]
    private ?\DateTimeInterface $returnedAt = null;

    #[ORM\Column(length: 20)]
    #[Groups(['borrowing:read', 'borrowing:write'])]
    private ?string $status = 'active'; // active, pending_return, returned, overdue

    public function __construct()
    {
        $this->borrowedAt = new \DateTime();
        $this->dueDate = (new \DateTime())->modify('+14 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;
        return $this;
    }

    public function getBorrowedAt(): ?\DateTimeInterface
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(\DateTimeInterface $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getReturnedAt(): ?\DateTimeInterface
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeInterface $returnedAt): static
    {
        $this->returnedAt = $returnedAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
