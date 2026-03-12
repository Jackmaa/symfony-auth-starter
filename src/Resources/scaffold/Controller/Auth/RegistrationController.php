<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'app_verify_email',
                (string) $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()],
            );

            $email = (new TemplatedEmail())
                ->from(new Address($this->getParameter('app.from_email'), $this->getParameter('app.from_name')))
                ->to($user->getEmail())
                ->subject('Please confirm your email')
                ->htmlTemplate('auth/email/verification.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                    'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                ]);

            // Uncomment the following line to send verification emails:
            // $mailer->send($email);

            return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $user->getId(),
                $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
