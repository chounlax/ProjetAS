<?php

namespace App\Entity;

use App\Repository\PasswordHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Empreinte (hash) d'un mot de passe déjà utilisé par un utilisateur.
 * Sert à refuser la réutilisation d'un ancien mot de passe.
 * Seul le hash est conservé, jamais le mot de passe en clair.
 */
#[ORM\Entity(repositoryClass: PasswordHistoryRepository::class)]
class PasswordHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'passwordHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(string $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        $this->createdAt = new \DateTimeImmutable();
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

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
