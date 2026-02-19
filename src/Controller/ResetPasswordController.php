<?php

declare(strict_types=1);

namespace Valentin\AuthStarter\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Handles password reset requests and resets.
 */
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * Display & process form to request a password reset.
     */
    public function request(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Implement password reset request
        // 1. Create form for email input
        // 2. Find user by email
        // 3. Generate reset token
        // 4. Send email with reset link
        // 5. Redirect to check email page

        return $this->render('@AuthStarter/forgot_password.html.twig');
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function checkEmail(): Response
    {
        return $this->render('@AuthStarter/check_email.html.twig');
    }

    /**
     * Validates and processes the reset URL that the user clicked in their email.
     */
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        string $token
    ): Response {
        // TODO: Implement password reset
        // 1. Validate token
        // 2. Create form for new password
        // 3. Hash and save new password
        // 4. Remove used token
        // 5. Auto-login user
        // 6. Redirect to success page

        return $this->render('@AuthStarter/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}