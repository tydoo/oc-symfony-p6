<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\ResetPasswordType;
use App\Security\EmailVerifier;
use App\Form\ForgotPasswordType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController {
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route(path: '/login', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home.home');
        }

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);
        $form->get('username')->setData($authenticationUtils->getLastUsername());

        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', [
            'loginForm' => $form,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'security.logout', methods: ['GET'])]
    public function logout(): void {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'security.register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->em->persist($user);
            $this->em->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'security.verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@snowtricks.fr', 'SnowTricks'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre email')
                    ->htmlTemplate('security/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Un email de confirmation a été envoyé à votre adresse email.');

            return $this->redirectToRoute('security.register');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'security.verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator,
        Security $security
    ): RedirectResponse {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('home.home');
        }

        $user = $this->userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('home.home');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('security.register');
        }

        $security->login($user, LoginFormAuthenticator::class);

        $this->addFlash('success', 'Votre email a été vérifié. Vous êtes maintenant connecté.');

        return $this->redirectToRoute('home.home');
    }

    #[Route('/forgot-password', name: 'security.forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $this->userRepository->findOneBy(['username' => $username]);
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            if ($user) {
                $user->setForgotPasswordToken($token);
                $this->em->flush();
                $mailer->send((new TemplatedEmail())
                    ->from(new Address('noreply@snowtricks.fr', 'SnowTricks'))
                    ->to($user->getEmail())
                    ->context([
                        'resetPasswordToken' => $token,
                        'signedUrl' => $this->generateUrl('security.reset_password', [
                            'token' => $token,
                            'email' => $user->getEmail()
                        ], UrlGeneratorInterface::ABSOLUTE_URL),
                    ])
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('security/reset_password_email.html.twig'));
            }

            $this->addFlash('success', 'Si votre adresse email est associée à un compte, un email vous a été envoyé pour réinitialiser votre mot de passe.');

            return $this->redirectToRoute('security.forgot_password');
        }

        return $this->render('security/forgot_password.html.twig', [
            'forgotPasswordForm' => $form,
        ]);
    }

    #[Route('/reset-password', name: 'security.reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        #[MapQueryParameter] string $token,
        #[MapQueryParameter] string $email,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user || $user->getForgotPasswordToken() !== $token) {
            $this->addFlash('danger', 'Token invalide');

            return $this->redirectToRoute('security.forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->upgradePassword(
                $user,
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setForgotPasswordToken(null);
            $this->em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');

            return $this->redirectToRoute('security.login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetPasswordForm' => $form,
        ]);
    }
}
