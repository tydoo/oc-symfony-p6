<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MesagesController extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/mesages/delete/{id}', name: 'messages.delete', methods: ['GET'])]
    public function delete(Message $message, Request $request): Response {
        if ($message->getUser() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce message !');
        } else {
            $this->em->remove($message);
            $this->em->flush();

            $this->addFlash('success', 'Le message a bien été supprimé !');
        }
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home.home'));
    }

    #[Route('/mesages/report/{id}', name: 'messages.report', methods: ['GET'])]
    public function report(Message $message, Request $request): Response {
        $message->setReported(true);
        $this->em->flush();

        $this->addFlash('success', 'Le message a bien été signalé !');

        return $this->redirect($request->headers->get('referer'));
    }
}
