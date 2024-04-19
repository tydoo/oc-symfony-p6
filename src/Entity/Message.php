<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function setUser(?User $User): static {
        $this->user = $User;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    /**
     * Do not call this method directly. It is only used by Doctrine events.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function setMessage(string $message): static {
        $this->message = $message;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist() {
        $this->createdAt = $this->createdAt ?? new DateTimeImmutable();
    }
}
