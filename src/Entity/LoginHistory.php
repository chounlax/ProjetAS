<?php

namespace App\Entity;

use App\Repository\LoginHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une tentative de connexion d'un utilisateur (réussie ou échouée),
 * affichée dans l'espace administrateur.
 */
#[ORM\Entity(repositoryClass: LoginHistoryRepository::class)]
#[ORM\Index(name: 'IDX_LOGIN_HISTORY_DATE', columns: ['connected_at'])]
class LoginHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'loginHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $connectedAt = null;

    #[ORM\Column]
    private bool $success = true;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    public function __construct(User $user, bool $success, ?string $ipAddress, ?string $userAgent)
    {
        $this->user = $user;
        $this->success = $success;
        $this->ipAddress = $ipAddress;
        $this->userAgent = null !== $userAgent ? mb_substr($userAgent, 0, 255) : null;
        $this->connectedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getConnectedAt(): ?\DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
