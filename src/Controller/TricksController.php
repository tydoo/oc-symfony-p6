<?php

namespace App\Controller;

use App\Repository\FigureRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TricksController extends AbstractController {

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route(
        path: '/tricks/{id}-{slug}',
        name: 'tricks.show',
        methods: ['GET'],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function show(
        int $id,
        string $slug,
        FigureRepository $figureRepository
    ): Response {
        $figure = $figureRepository->find($id);
        if ($figure->getSlug() !== $slug) {
            return $this->createNotFoundException('Aucune figure ne correspond à ce slug ou cet id.');
        }

        return $this->render('tricks/show.html.twig', [
            'figure' => $figure,
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
        string $slug,
        FigureRepository $figureRepository
    ): Response {
        $figure = $figureRepository->find($id);
        if ($figure->getSlug() !== $slug) {
            return $this->createNotFoundException('Aucune figure ne correspond à ce slug ou cet id.');
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
        string $slug,
        FigureRepository $figureRepository
    ): Response {
        $figure = $figureRepository->find($id);
        if ($figure->getSlug() !== $slug) {
            return $this->createNotFoundException('Aucune figure ne correspond à ce slug ou cet id.');
        }

        $this->em->remove($figure);
        $this->em->flush();

        $this->addFlash('success', 'La figure a bien été supprimée !');

        return $this->redirectToRoute('home.home');
    }
}
