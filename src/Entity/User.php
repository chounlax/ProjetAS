<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse e-mail.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Rôles proposés dans l'espace administrateur (libellé => rôle).
     * Pour un nouveau projet, il suffit d'ajouter une ligne ici
     * et la hiérarchie correspondante dans config/packages/security.yaml.
     */
    public const ROLES = [
        'Utilisateur' => 'ROLE_USER',
        'Administrateur' => 'ROLE_ADMIN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre adresse e-mail.')]
    #[Assert\Email(message: "L'adresse e-mail « {{ value }} » n'est pas valide.")]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre nom.')]
    #[Assert\Length(min: 2, max: 55, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/u", message: 'Le nom contient des caractères invalides.')]
    private ?string $nom = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre prénom.')]
    #[Assert\Length(min: 2, max: 55, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/u", message: 'Le prénom contient des caractères invalides.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre adresse.')]
    #[Assert\Length(min: 5, max: 100, minMessage: "L'adresse doit contenir au moins {{ limit }} caractères.", maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $adresse = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre ville.')]
    #[Assert\Length(max: 55, maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/u", message: 'La ville contient des caractères invalides.')]
    private ?string $ville = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre code postal.')]
    #[Assert\Regex(pattern: '/^\d{5}$/', message: 'Le code postal doit contenir exactement 5 chiffres.')]
    private ?string $codePostal = null;

    /** Un compte suspendu ne peut plus se connecter. */
    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, LoginHistory>
     */
    #[ORM\OneToMany(targetEntity: LoginHistory::class, mappedBy: 'user', orphanRemoval: true)]
    #[ORM\OrderBy(['connectedAt' => 'DESC'])]
    private Collection $loginHistories;

    /**
     * @var Collection<int, PasswordHistory>
     */
    #[ORM\OneToMany(targetEntity: PasswordHistory::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $passwordHistories;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->loginHistories = new ArrayCollection();
        $this->passwordHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function isAdmin(): bool
    {
        return \in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getInitiales(): string
    {
        return mb_strtoupper(mb_substr((string) $this->prenom, 0, 1) . mb_substr((string) $this->nom, 0, 1));
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, LoginHistory>
     */
    public function getLoginHistories(): Collection
    {
        return $this->loginHistories;
    }

    /**
     * Dernière connexion réussie, ou null.
     */
    public function getLastLogin(): ?LoginHistory
    {
        foreach ($this->loginHistories as $login) {
            if ($login->isSuccess()) {
                return $login;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, PasswordHistory>
     */
    public function getPasswordHistories(): Collection
    {
        return $this->passwordHistories;
    }

    public function addPasswordHistory(PasswordHistory $passwordHistory): static
    {
        if (!$this->passwordHistories->contains($passwordHistory)) {
            $this->passwordHistories->add($passwordHistory);
            $passwordHistory->setUser($this);
        }

        return $this;
    }
}
