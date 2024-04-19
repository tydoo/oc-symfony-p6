<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class SecurityService {
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $em
    ) {
    }

    public function sendEmailConfirmation(User $user): void {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'security.verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $this->mailer->send((new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Merci de confirmer votre email')
            ->htmlTemplate('security/confirmation_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
            ]));
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        $user->setVerified(true);
        $this->em->flush();
    }

    public function sendEmailForgotPassword(User $user): void {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'security.reset_password',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $this->mailer->send((new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('security/reset_password_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
            ]));
    }
}
