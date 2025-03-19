<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfProtectionListener implements EventSubscriberInterface
{
    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Appliquer la protection CSRF uniquement sur les requêtes non GET
        if (!$request->isMethodSafe()) { 
            $submittedToken = $request->headers->get('X-CSRF-Token'); // Récupération depuis le header

            if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('ajax', $submittedToken))) {
                $event->setResponse(new JsonResponse(['error' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN));
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
