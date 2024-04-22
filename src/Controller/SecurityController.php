<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\ResetPasswordType;
use App\Form\ForgotPasswordType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\SecurityService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route(name: 'security', methods: ['GET', 'POST'])]
class SecurityController extends AbstractController {
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/login', name: '.login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $this->userIsLoggedIn();

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);
        $form->get('username')->setData($authenticationUtils->getLastUsername());

        return $this->render('security/login.html.twig', [
            'loginForm' => $form,
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/logout', name: '.logout', methods: ['GET'])]
    public function logout(): void {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: '.register')]
    public function register(
        Request $request
    ): Response {
        $this->userIsLoggedIn();

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userRepository->add($user, $form->get('plainPassword')->getData());

            $this->addFlash('success', 'Un email de confirmation a été envoyé à votre adresse email.');

            return $this->redirectToRoute('security.register');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: '.verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        #[MapQueryParameter] int $id,
        Request $request,
        Security $security
    ): RedirectResponse {
        $this->userIsLoggedIn();

        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->redirectToRoute('home.home');
        }

        try {
            $this->securityService->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('security.register');
        }

        $this->addFlash('success', 'Votre email a été vérifié. Vous êtes maintenant connecté.');

        return $security->login($user, LoginFormAuthenticator::class);
    }

    #[Route('/forgot-password', name: '.forgot_password')]
    public function forgotPassword(Request $request): Response {
        $this->userIsLoggedIn();

        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['username' => $form->get('username')->getData()]);

            if ($user) {
                $this->securityService->sendEmailForgotPassword($user);
            }

            $this->addFlash('success', 'Si votre adresse email est associée à un compte, un email vous a été envoyé pour réinitialiser votre mot de passe.');

            return $this->redirectToRoute('security.forgot_password');
        }

        return $this->render('security/forgot_password.html.twig', [
            'forgotPasswordForm' => $form,
        ]);
    }

    #[Route('/reset-password', name: '.reset_password')]
    public function resetPassword(
        #[MapQueryParameter] int $id,
        Request $request
    ): Response {
        $this->userIsLoggedIn();

        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->redirectToRoute('home.home');
        }

        try {
            $this->securityService->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', 'Ce lien de réinitialisation de mot de passe a expiré.');
            return $this->redirectToRoute('security.forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->upgradePassword($user, $form->get('password')->getData());

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');

            return $this->redirectToRoute('security.login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetPasswordForm' => $form,
        ]);
    }

    private function userIsLoggedIn(): ?RedirectResponse {
        return $this->getUser() ? $this->redirectToRoute('home.home') : null;
    }
}
