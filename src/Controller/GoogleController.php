<?php

declare(strict_types=1);

namespace Valentin\AuthStarter\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles Google OAuth authentication.
 */
class GoogleController extends AbstractController
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry
    ) {
    }

    /**
     * Link to this controller to start the OAuth flow.
     */
    public function connect(): RedirectResponse
    {
        // Redirect to Google OAuth
        return $this->clientRegistry
            ->getClient('google')
            ->redirect([
                'email',
                'profile',
            ]);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    public function connectCheck(Request $request): never
    {
        // This method will never be called - the authenticator handles this
        // But the route must exist for the OAuth flow to work
        throw new \LogicException('This method should never be reached. Check your security.yaml configuration.');
    }
}