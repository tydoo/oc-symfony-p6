<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository {

    private ?User $user;

    public function __construct(
        ManagerRegistry $registry,
        private readonly Security $security
    ) {
        parent::__construct($registry, Message::class);
        $this->user = $this->security->getUser();
    }

    public function add(Message $message): Message {
        $message->setUser($this->user);
        $this->_em->persist($message);
        $this->_em->flush();

        return $message;
    }

    public function delete(Message $message): void {
        $this->_em->remove($message);
        $this->_em->flush();
    }

    public function report(Message $message): Message {
        $message->setReported(true);
        $this->_em->flush();

        return $message;
    }
}
