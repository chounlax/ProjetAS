<?php

namespace App\EventSubscriber;

use App\Entity\LoginHistory;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Enregistre chaque tentative de connexion (date, heure, IP, navigateur)
 * pour l'historique affiché dans l'espace administrateur.
 */
final class LoginHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $this->record($user, true, $event->getRequest());
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        // On n'enregistre un échec que si l'adresse e-mail correspond à un compte existant.
        $passport = $event->getPassport();
        if (null === $passport || !$passport->hasBadge(UserBadge::class)) {
            return;
        }

        /** @var UserBadge $badge */
        $badge = $passport->getBadge(UserBadge::class);
        $user = $this->userRepository->findOneBy(['email' => $badge->getUserIdentifier()]);

        if ($user instanceof User) {
            $this->record($user, false, $event->getRequest());
        }
    }

    private function record(User $user, bool $success, Request $request): void
    {
        $this->entityManager->persist(new LoginHistory(
            $user,
            $success,
            $request->getClientIp(),
            $request->headers->get('User-Agent'),
        ));
        $this->entityManager->flush();
    }
}
