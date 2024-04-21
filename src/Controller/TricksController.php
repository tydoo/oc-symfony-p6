<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Form\TricksType;
use App\Form\CreateMessageType;
use Doctrine\ORM\EntityManager;
use App\Repository\PhotoRepository;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

class TricksController extends AbstractController {

    private readonly string $uploadFQDNDir;

    public function __construct(
        private readonly string $projectDir,
        private readonly string $uploadDir,
        private readonly EntityManagerInterface $em,
        private readonly FigureRepository $figureRepository,
        private readonly Filesystem $filesystem,
        private readonly PhotoRepository $photoRepository,
    ) {
        $this->uploadFQDNDir = $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->uploadDir;
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
        $figure = $this->getFigureFromIdAndSlug($id, $slug);

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
        $figure = $this->getFigureFromIdAndSlug($id, $slug);

        foreach ($figure->getPhotos() as $photo) {
            $this->filesystem->remove($this->uploadFQDNDir . DIRECTORY_SEPARATOR . $photo->getPath());
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
        $figure = $this->getFigureFromIdAndSlug($id, $slug);

        $form = $this->createForm(TricksType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'La figure a bien été modifiée !');

            return $this->redirectToRoute('tricks.show', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('tricks/edit.html.twig', [
            'figure' => $figure,
            'tricksForm' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/{id}-{slug}/delete-featured-image',
        name: 'tricks.delete_featured_image',
        methods: ['GET']
    )]
    public function deleteFeaturedImage(
        int $id,
        string $slug
    ): Response {
        $figure = $this->getFigureFromIdAndSlug($id, $slug);

        $path = str_replace('upload/', '', $figure->getFeaturedPhoto());
        $photo = $this->photoRepository->findOneBy(['path' => $path]);
        if (!$photo) {
            throw $this->createNotFoundException('Aucune image trouvée !');
        }

        $this->em->remove($photo);
        $this->em->flush();

        $this->filesystem->remove($this->uploadFQDNDir . DIRECTORY_SEPARATOR . $path);

        $this->addFlash('success', 'L\'image à la une a bien été supprimée !');

        return $this->redirectToRoute('tricks.edit', [
            'id' => $figure->getId(),
            'slug' => $figure->getSlug(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/new',
        name: 'tricks.create',
        methods: ['GET', 'POST']
    )]
    public function create(Request $request): Response {
        $figure = new Figure();
        $form = $this->createForm(TricksType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $figure->setCreatedBy($this->getUser());
            $figure->setUpdatedBy($this->getUser());
            $this->em->persist($figure);
            $this->em->flush();

            $this->addFlash('success', 'La figure a bien été créée ! Vous pouvez maintenant ajouter des images et des vidéos.');

            return $this->redirectToRoute('tricks.edit', [
                'id' => $figure->getId(),
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('tricks/edit.html.twig', [
            'figure' => $figure,
            'tricksForm' => $form,
        ]);
    }

    private function getFigureFromIdAndSlug(int $id, string $slug): Figure {
        $figure = $this->figureRepository->find($id);
        if (!$figure || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException('Aucune figure trouvé !');
        }

        return $figure;
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/tricks/photos/{path}/delete',
        name: 'tricks.delete_photo',
        methods: ['GET']
    )]
    public function delete_photo(string $path): Response {
        $photo = $this->photoRepository->findOneBy(['path' => $path]);
        if (!$photo) {
            throw $this->createNotFoundException('Aucune image trouvée !');
        }

        $this->filesystem->remove($this->uploadFQDNDir . DIRECTORY_SEPARATOR . $path);

        $this->em->remove($photo);
        $this->em->flush();

        $this->addFlash('success', 'La photo a bien été supprimée !');

        return $this->redirectToRoute('tricks.edit', [
            'id' => $photo->getFigure()->getId(),
            'slug' => $photo->getFigure()->getSlug(),
        ]);
    }
}
