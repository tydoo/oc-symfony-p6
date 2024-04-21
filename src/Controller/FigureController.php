<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Message;
use App\Form\TricksType;
use App\Form\CreateMessageType;
use App\Form\FeaturedPhotoType;
use App\Repository\PhotoRepository;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route(path: '/figure', name: 'figure', methods: ['GET', 'POST'])]
class FigureController extends AbstractController {


    public function __construct(
        private readonly FigureRepository $figureRepository,
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/new', name: '.create')]
    public function create(Request $request): Response {
        $figure = new Figure();
        $form = $this->createForm(TricksType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $figure = $this->figureRepository->add($figure);

            $this->addFlash('success', 'La figure a bien été créée ! Vous pouvez maintenant ajouter des images et des vidéos.');

            return $this->redirectToRoute('figure.update', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('figure/create-update.html.twig', [
            'figure' => $figure,
            'tricksForm' => $form,
        ]);
    }

    #[Route(path: '/{id<\d+>}-{slug<[a-z0-9-]+>}', name: '.read')]
    public function read(
        int $id,
        string $slug,
        #[MapQueryParameter] ?string $page,
        MessageRepository $messageRepository,
        Request $request
    ): Response {
        $figure = $this->figureRepository->getFigureFromIdAndSlug($id, $slug);

        $message = new Message();
        $form = $this->createForm(CreateMessageType::class, $message);
        $form->handleRequest($request);

        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            $message = $messageRepository->add($message);

            $this->addFlash('success', 'Votre message a bien été ajouté !');

            return $this->redirectToRoute('figure.read', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        $page = $page ?: 1;

        $messagesCount = $messageRepository->count([]);
        $messages = $messageRepository->findBy([], ['createdAt' => 'DESC'], 10 * $page);
        $lastPage = (int) ceil($messagesCount / 10);

        return $this->render('figure/read.html.twig', [
            'figure' => $figure,
            'messages' => $messages,
            'createMessageForm' => $form,
            'page' => $page,
            'lastPage' => $lastPage,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/{id<\d+>}-{slug<[a-z0-9-]+>}/update', name: '.update')]
    public function update(
        int $id,
        string $slug,
        Request $request
    ): Response {
        $figure = $this->figureRepository->getFigureFromIdAndSlug($id, $slug);

        //formulaire principal
        $form = $this->createForm(TricksType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->figureRepository->update($figure);

            $this->addFlash('success', 'La figure a bien été modifiée !');

            return $this->redirectToRoute('figure.read', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        //formulaire d'ajout de photo à la une
        $featuredPhotoForm = $this->createForm(FeaturedPhotoType::class);
        $featuredPhotoForm->handleRequest($request);
        if ($featuredPhotoForm->isSubmitted() && $featuredPhotoForm->isValid()) {
            $this->photoRepository->add(
                featuredPhotoNew: $featuredPhotoForm->getData()['photo'],
                figure: $figure,
                isFeatured: true
            );

            $this->addFlash('success', 'L\'image à la une a bien été modifiée !');

            return $this->redirectToRoute('figure.update', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('figure/create-update.html.twig', [
            'figure' => $figure,
            'tricksForm' => $form,
            'featuredPhotoForm' => $featuredPhotoForm,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/{id<\d+>}-{slug<[a-z0-9-]+>}/delete', name: '.delete', methods: ['GET'])]
    public function delete(
        int $id,
        string $slug
    ): RedirectResponse {
        $figure = $this->figureRepository->getFigureFromIdAndSlug($id, $slug);

        $this->figureRepository->delete($figure);

        $this->addFlash('success', 'La figure a bien été supprimée !');

        return $this->redirectToRoute('home.home');
    }
}
