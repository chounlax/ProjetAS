<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Refuse la connexion d'un compte suspendu par un administrateur.
 *
 * La vérification est faite APRÈS le contrôle du mot de passe (checkPostAuth) :
 * un attaquant qui ne connaît pas le mot de passe ne peut pas savoir
 * qu'un compte existe et qu'il est suspendu.
 */
final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if ($user instanceof User && !$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été suspendu. Contactez un administrateur.');
        }
    }
}
