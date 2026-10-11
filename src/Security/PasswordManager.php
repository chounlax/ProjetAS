<?php

namespace App\Security;

use App\Entity\PasswordHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Point unique pour définir le mot de passe d'un utilisateur.
 *
 * Chaque nouveau mot de passe est haché puis son empreinte est ajoutée
 * à l'historique : on peut ainsi refuser la réutilisation d'un ancien mot de passe.
 */
final class PasswordManager
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Vrai si ce mot de passe est le mot de passe actuel ou un ancien mot de passe de l'utilisateur.
     */
    public function isAlreadyUsed(User $user, string $plainPassword): bool
    {
        $hasher = $this->hasherFactory->getPasswordHasher($user);

        if (null !== $user->getPassword() && $hasher->verify($user->getPassword(), $plainPassword)) {
            return true;
        }

        foreach ($user->getPasswordHistories() as $history) {
            if ($hasher->verify($history->getPasswordHash(), $plainPassword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hache et enregistre le nouveau mot de passe, et l'ajoute à l'historique.
     * Ne fait pas de flush : l'appelant enregistre en base.
     */
    public function setPassword(User $user, string $plainPassword): void
    {
        $hash = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hash);

        $history = new PasswordHistory($hash);
        $user->addPasswordHistory($history);
        $this->entityManager->persist($history);
    }
}
