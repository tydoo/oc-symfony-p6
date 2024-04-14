<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * 1: photo
     * 2: video
     */
    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Figure $figure = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): static {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setUrl(string $url): static {
        $this->url = $url;

        return $this;
    }

    public function getFigure(): ?Figure {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): static {
        $this->figure = $figure;

        return $this;
    }

    public function isPhoto(): bool {
        return $this->type === 1;
    }

    public function isVideo(): bool {
        return $this->type === 2;
    }
}
