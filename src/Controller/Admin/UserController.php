<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Security\PasswordManager;
use App\Validator\StrongPassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Gestion des comptes, rôles et habilitations par l'administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/utilisateurs')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordManager $passwordManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, ['password_required' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->applyPassword($form, $user)) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('Le compte de %s a été créé.', $user->getNomComplet()));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'title' => 'Ajouter un utilisateur',
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_user_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(AdminUserType::class, $user, ['password_required' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // un administrateur ne peut ni se suspendre ni se retirer ses propres droits
            if ($user === $this->getUser() && (!$user->isActive() || !$user->isAdmin())) {
                $form->addError(new FormError('Vous ne pouvez pas suspendre votre propre compte ni retirer votre rôle administrateur.'));
            } elseif ($this->applyPassword($form, $user)) {
                $this->entityManager->flush();
                $this->addFlash('success', sprintf('Le compte de %s a été modifié.', $user->getNomComplet()));

                return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
            }
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'title' => 'Modifier ' . $user->getNomComplet(),
        ]);
    }

    #[Route('/{id}/suspendre', name: 'admin_user_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . $user->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        } else {
            $user->setIsActive(!$user->isActive());
            $this->entityManager->flush();
            $this->addFlash('success', sprintf(
                'Le compte de %s a été %s.',
                $user->getNomComplet(),
                $user->isActive() ? 'réactivé' : 'suspendu'
            ));
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/supprimer', name: 'admin_user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        } else {
            $name = $user->getNomComplet();
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('Le compte de %s a été supprimé.', $name));
        }

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Valide et applique le mot de passe saisi par l'administrateur.
     * Retourne false (avec une erreur sur le formulaire) si le mot de passe est refusé.
     */
    private function applyPassword(FormInterface $form, User $user): bool
    {
        /** @var string|null $plainPassword */
        $plainPassword = $form->get('plainPassword')->getData();

        if (null === $plainPassword || '' === $plainPassword) {
            if (null === $user->getPassword()) {
                $form->get('plainPassword')->get('first')->addError(new FormError('Veuillez saisir un mot de passe.'));

                return false;
            }

            return true; // modification sans changement de mot de passe
        }

        $violations = $this->validator->validate($plainPassword, new StrongPassword());
        foreach ($violations as $violation) {
            $form->get('plainPassword')->get('first')->addError(new FormError((string) $violation->getMessage()));
        }
        if (\count($violations) > 0) {
            return false;
        }

        if (null !== $user->getId() && $this->passwordManager->isAlreadyUsed($user, $plainPassword)) {
            $form->get('plainPassword')->get('first')->addError(new FormError('Cet utilisateur a déjà utilisé ce mot de passe.'));

            return false;
        }

        $this->passwordManager->setPassword($user, $plainPassword);

        return true;
    }
}
