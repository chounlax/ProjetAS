<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Déconnecte immédiatement un utilisateur déjà connecté
 * dont le compte vient d'être suspendu par un administrateur.
 */
final class SuspendedAccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // priorité inférieure au pare-feu (8) : l'utilisateur est déjà chargé
        return [KernelEvents::REQUEST => ['onKernelRequest', 0]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->isActive()) {
            return;
        }

        $this->security->logout(false);

        $session = $event->getRequest()->getSession();
        $session->getFlashBag()->add('error', 'Votre compte a été suspendu. Contactez un administrateur.');

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
    }
}
