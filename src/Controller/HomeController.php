<?php

namespace App\Controller;

use App\Repository\FigureRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {

    #[Route('/', name: 'home.index', methods: ['GET'])]
    public function index(): RedirectResponse {
        return $this->redirectToRoute('home.home');
    }

    #[Route('/home', name: 'home.home', methods: ['GET'])]
    public function home(
        FigureRepository $figureRepository,
        #[MapQueryParameter] ?string $page,
    ): Response {
        $page = $page ?: 1;

        $figureCount = $figureRepository->count([]);
        $figures = $figureRepository->findBy([], ['createdAt' => 'DESC'], 15 * $page);
        $lastPage = (int) ceil($figureCount / 15);

        return $this->render('home.html.twig', [
            'figures' => $figures,
            'page' => $page,
            'lastPage' => $lastPage,
        ]);
    }
}
