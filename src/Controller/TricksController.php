<?php

namespace App\Controller;

use App\Form\CreateMessageType;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TricksController extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FigureRepository $figureRepository
    ) {
    }

    #[Route(
        path: '/tricks/{id}-{slug}',
        name: 'tricks.show',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function show(
        int $id,
        string $slug,
        MessageRepository $messageRepository,
        Request $request
    ): Response {
        $figure = $this->figureRepository->find($id);
        if (!$figure || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException('Aucune figure trouvé !');
        }

        $form = $this->createForm(CreateMessageType::class);
        $form->handleRequest($request);

        if (
            $this->isGranted('ROLE_USER') &&
            $form->isSubmitted() &&
            $form->isValid()
        ) {
            $message = $form->getData();
            $message->setUser($this->getUser());
            $this->em->persist($message);
            $this->em->flush();

            $this->addFlash('success', 'Votre message a bien été ajouté !');

            return $this->redirectToRoute('tricks.show', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('tricks/show.html.twig', [
            'figure' => $figure,
            'messages' => $messageRepository->findAll(),
            'createMessageForm' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/{id}-{slug}/edit',
        name: 'tricks.edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function edit(
        int $id,
        string $slug
    ): Response {
        $figure = $this->figureRepository->find($id);
        if (!$figure || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException('Aucune figure trouvé !');
        }

        return $this->render('tricks/edit.html.twig', [
            'figure' => $figure,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/{id}-{slug}/delete',
        name: 'tricks.delete',
        methods: ['GET'],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function delete(
        int $id,
        string $slug
    ): Response {
        $figure = $this->figureRepository->find($id);
        if (!$figure || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException('Aucune figure trouvé !');
        }

        $this->em->remove($figure);
        $this->em->flush();

        $this->addFlash('success', 'La figure a bien été supprimée !');

        return $this->redirectToRoute('home.home');
    }
}
