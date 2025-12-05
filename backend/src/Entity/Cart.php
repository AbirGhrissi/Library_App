<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['cart:read']],
    denormalizationContext: ['groups' => ['cart:write']]
)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'cart')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cart:read', 'cart:write'])]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['cart:read', 'cart:write'])]
    private Collection $items;

    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function removeItem(CartItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getCart() === $this) {
                $item->setCart(null);
            }
        }
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getBook()->getPrice() * $item->getQuantity();
        }
        return $total;
    }

    public function clear(): void
    {
        $this->items->clear();
        $this->updatedAt = new \DateTimeImmutable();
    }
}
