<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * Politique de mot de passe de l'application, réutilisable partout
 * (inscription, changement de mot de passe, espace administrateur).
 *
 * - 12 caractères minimum
 * - au moins une minuscule, une majuscule, un chiffre et un caractère spécial
 * - assez difficile à deviner (PasswordStrength)
 * - absent des fuites de données connues (NotCompromisedPassword, API Have I Been Pwned)
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class StrongPassword extends Compound
{
    public const MIN_LENGTH = 12;

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(message: 'Veuillez saisir un mot de passe.'),
            new Assert\Length(
                min: self::MIN_LENGTH,
                max: 4096,
                minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
            ),
            new Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir au moins une lettre minuscule.'),
            new Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une lettre majuscule.'),
            new Assert\Regex(pattern: '/\d/', message: 'Le mot de passe doit contenir au moins un chiffre.'),
            new Assert\Regex(pattern: '/[^a-zA-Z\d]/', message: 'Le mot de passe doit contenir au moins un caractère spécial.'),
            new Assert\PasswordStrength(
                minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                message: 'Ce mot de passe est trop facile à deviner. Allongez-le ou rendez-le plus varié.',
            ),
            // skipOnError : si l'API est injoignable, on n'empêche pas l'inscription.
            new Assert\NotCompromisedPassword(
                message: 'Ce mot de passe apparaît dans une fuite de données connue. Choisissez-en un autre.',
                skipOnError: true,
            ),
        ];
    }
}
