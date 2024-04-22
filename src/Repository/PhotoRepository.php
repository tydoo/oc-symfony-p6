<?php

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\Figure;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Photo>
 *
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository {

    private string $uploadFQDNDir;

    public function __construct(
        private readonly string $projectDir,
        ManagerRegistry $registry,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct($registry, Photo::class);
        $this->uploadFQDNDir = $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload';
    }

    public function delete(Photo $photo): void {
        $this->filesystem->remove($this->uploadFQDNDir . DIRECTORY_SEPARATOR . $photo->getPath());
        $this->_em->remove($photo);
        $this->_em->flush();
    }

    public function add(UploadedFile $photo, Figure $figure, bool $isFeatured = false): Photo {
        if ($isFeatured) {
            $featuredPhotoActuel = $this->findOneBy(['path' => $figure->getFeaturedPhoto()]);
            if ($featuredPhotoActuel && $figure->haveFeaturedPhoto()) {
                $this->delete($featuredPhotoActuel);
            }
        }

        $name = 'tricks-' . bin2hex(random_bytes(16)) . '.' . $photo->guessExtension();
        $photo->move($this->uploadFQDNDir, $name);
        $photo = (new Photo())
            ->setPath($name)
            ->setFigure($figure)
            ->setFeatured($isFeatured);

        $this->_em->persist($photo);
        $this->_em->flush();

        return $photo;
    }

    public function getPhotosFromFigure(Figure $figure): array {
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.figure = :figure')
            ->setParameter('figure', $figure)
            ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
