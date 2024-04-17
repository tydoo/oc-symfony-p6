<?php

namespace App\Controller;

use App\Repository\FigureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {

    #[Route('/', name: 'home.index', methods: ['GET'])]
    public function index(): RedirectResponse {
        return $this->redirectToRoute('home.home');
    }

    #[Route('/home', name: 'home.home', methods: ['GET'])]
    public function home(FigureRepository $figureRepository): Response {
        return $this->render('home.html.twig', [
            'figures' => $figureRepository->findAll(),
        ]);
    }
}
