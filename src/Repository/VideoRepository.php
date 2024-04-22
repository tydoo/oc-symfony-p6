<?php

namespace App\Repository;

use App\Entity\Video;
use App\Entity\Figure;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Video::class);
    }

    public function delete(Video $video): void {
        $this->_em->remove($video);
        $this->_em->flush();
    }

    public function getVideosFromFigure(Figure $figure): array {
        $qb = $this->createQueryBuilder('v');

        $qb->where('v.figure = :figure')
            ->setParameter('figure', $figure)
            ->orderBy('v.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
