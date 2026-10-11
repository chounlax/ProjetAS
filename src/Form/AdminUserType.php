<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de l'administrateur pour créer ou modifier un compte.
 * Le mot de passe est obligatoire à la création, facultatif à la modification ;
 * sa validation (StrongPassword) est faite dans le contrôleur.
 */
class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail'])
            ->add('adresse', TextType::class, ['label' => 'Adresse'])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['inputmode' => 'numeric', 'maxlength' => 5],
            ])
            ->add('ville', TextType::class, ['label' => 'Ville'])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles et habilitations',
                'choices' => User::ROLES,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Compte actif (décocher pour suspendre)',
                'required' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => $options['password_required'],
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => $options['password_required'] ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour ne pas le changer)',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'password_required' => true,
        ]);
        $resolver->setAllowedTypes('password_required', 'bool');
    }
}
