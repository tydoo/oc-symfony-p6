<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/videos', name: 'videos', methods: ['GET'])]
class VideoController extends AbstractController {

    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
    }

    #[Route('/{id<\d+>}/delete', name: '.delete')]
    public function delete(Video $video): RedirectResponse {
        $this->videoRepository->delete($video);

        $this->addFlash('success', 'La vidéo a bien été supprimée !');

        return $this->redirectToRoute('figure.update', [
            'id' => $video->getFigure()->getId(),
            'slug' => $video->getFigure()->getSlug(),
        ]);
    }
}
