<?php

declare(strict_types=1);

namespace Valentin\AuthStarter\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Valentin\AuthStarter\Entity\User;

/**
 * Handles user registration and email verification.
 */
class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // TODO: Implement registration form handling
        // 1. Create RegistrationFormType
        // 2. Handle form submission
        // 3. Hash password
        // 4. Save user
        // 5. Send verification email
        // 6. Redirect to check email page

        return $this->render('@AuthStarter/register.html.twig', [
            // 'registrationForm' => $form,
        ]);
    }

    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: Implement email verification
        // 1. Get user from token
        // 2. Verify signature
        // 3. Mark user as verified
        // 4. Auto-login user
        // 5. Redirect to success page

        return $this->redirectToRoute('app_home');
    }
}