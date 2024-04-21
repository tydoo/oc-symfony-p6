<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/mesages', name: 'messages', methods: ['GET', 'POST'])]
class MesagesController extends AbstractController {

    public function __construct(
        private readonly MessageRepository $messageRepository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/delete', name: '.delete', methods: ['GET'])]
    public function delete(Request $request, Message $message): RedirectResponse {
        if ($message->getUser() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce message !');
        } else {
            $this->messageRepository->delete($message);

            $this->addFlash('success', 'Le message a bien été supprimé !');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home.home'));
    }

    #[Route('/{id}/report', name: '.report', methods: ['GET'])]
    public function report(Request $request, Message $message): RedirectResponse {
        $message = $this->messageRepository->report($message);

        $this->addFlash('success', 'Le message a bien été signalé !');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home.home'));
    }
}
