<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Figure;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @extends ServiceEntityRepository<Figure>
 *
 * @method Figure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Figure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Figure[]    findAll()
 * @method Figure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FigureRepository extends ServiceEntityRepository {

    private ?User $user;
    private string $uploadFQDNDir;

    public function __construct(
        private readonly string $projectDir,
        ManagerRegistry $registry,
        private readonly Security $security,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct($registry, Figure::class);
        $this->user = $this->security->getUser();
        $this->uploadFQDNDir = $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload';
    }

    public function getFigureFromIdAndSlug(int $id, string $slug): Figure {
        $figure = $this->find($id);

        if ($figure === null || $figure->getSlug() !== $slug) {
            throw $this->createNotFoundException();
        }

        return $figure;
    }

    public function add(Figure $figure): Figure {
        $figure->setCreatedBy($this->user);
        $figure->setUpdatedBy($this->user);
        $this->_em->persist($figure);
        $this->_em->flush();

        return $figure;
    }

    public function delete(Figure $figure): void {
        foreach ($figure->getPhotos() as $photo) {
            $this->filesystem->remove($this->uploadFQDNDir . DIRECTORY_SEPARATOR . $photo->getPath());
        }

        $this->_em->remove($figure);
        $this->_em->flush();
    }

    public function update(Figure $figure): Figure {
        $figure->setUpdatedBy($this->user);
        $this->_em->flush();

        return $figure;
    }
}
