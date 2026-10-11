<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\StrongPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Les règles de validation des champs (nom, adresse, code postal…) sont
 * déclarées sur l'entité User : elles s'appliquent ainsi aussi dans l'espace administrateur.
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['autocomplete' => 'given-name'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['autocomplete' => 'family-name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['autocomplete' => 'street-address'],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'autocomplete' => 'postal-code',
                    'inputmode' => 'numeric',
                    'maxlength' => 5,
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => ['autocomplete' => 'address-level2'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // lu et haché dans le contrôleur, jamais stocké en clair
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'constraints' => [new StrongPassword()],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'data' => false,
                'label' => "J'accepte les conditions d'utilisation",
                'constraints' => [
                    new IsTrue(message: "Vous devez accepter les conditions d'utilisation."),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
