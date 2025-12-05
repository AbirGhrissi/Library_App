<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PurchaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['purchase:read']],
    denormalizationContext: ['groups' => ['purchase:write']],
    security: "is_granted('ROLE_USER')"
)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['purchase:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['purchase:read', 'purchase:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['purchase:read', 'purchase:write'])]
    private ?Book $book = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['purchase:read'])]
    private ?\DateTimeInterface $purchasedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['purchase:read', 'purchase:write'])]
    private ?string $price = null;

    #[ORM\Column]
    #[Groups(['purchase:read', 'purchase:write'])]
    private ?int $quantity = 1;

    #[ORM\Column(length: 20)]
    #[Groups(['purchase:read', 'purchase:write'])]
    private ?string $status = 'pending'; // pending, completed, cancelled

    public function __construct()
    {
        $this->purchasedAt = new \DateTime();
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

    public function getPurchasedAt(): ?\DateTimeInterface
    {
        return $this->purchasedAt;
    }

    public function setPurchasedAt(\DateTimeInterface $purchasedAt): static
    {
        $this->purchasedAt = $purchasedAt;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
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
