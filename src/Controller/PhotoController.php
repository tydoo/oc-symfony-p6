<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[IsGranted('ROLE_USER')]
#[Route(path: '/photos', name: 'photos', methods: ['GET'])]
class PhotoController extends AbstractController {

    public function __construct(
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    #[Route(path: '/{path<tricks-[a-f0-9]{32}\.(jpg|png|gif|jpeg)>}/delete', name: '.delete')]
    public function delete(string $path): RedirectResponse {
        $photo = $this->photoRepository->findOneBy(['path' => $path]);
        if (!$photo) {
            throw $this->createNotFoundException('Aucune image trouvée !');
        }

        $this->photoRepository->delete($photo);

        $this->addFlash('success', $photo->isFeatured() ? 'L\'image à la une a bien été supprimée !' : 'La photo a bien été supprimée !');

        return $this->redirectToRoute('figure.update', [
            'id' => $photo->getFigure()->getId(),
            'slug' => $photo->getFigure()->getSlug(),
        ]);
    }
}
