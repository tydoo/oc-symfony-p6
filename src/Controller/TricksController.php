<?php

namespace App\Controller;

use App\Form\CreateMessageType;
use App\Form\TricksType;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
        #[MapQueryParameter] ?string $page,
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

        $page = $page ?: 1;

        $messagesCount = $messageRepository->count([]);
        $messages = $messageRepository->findBy([], ['createdAt' => 'DESC'], 10 * $page);
        $lastPage = (int) ceil($messagesCount / 10);

        return $this->render('tricks/show.html.twig', [
            'figure' => $figure,
            'messages' => $messages,
            'createMessageForm' => $form,
            'page' => $page,
            'lastPage' => $lastPage,
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

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/{id}-{slug}/edit',
        name: 'tricks.edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function edit(
        int $id,
        string $slug,
        Request $request
    ): Response {
        $figure = $this->figureRepository->find($id);
        if (!$figure || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException('Aucune figure trouvé !');
        }

        $form = $this->createForm(TricksType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        }

        return $this->render('tricks/edit.html.twig', [
            'tricksForm' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/new',
        name: 'tricks.create',
        methods: ['GET', 'POST']
    )]
    public function create(): Response {
        return $this->render('tricks/edit.html.twig', []);
    }
}
