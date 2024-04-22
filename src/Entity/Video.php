<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Figure $figure = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getPath(): ?string {
        return $this->path;
    }

    public function setPath(string $path): static {
        $this->path = $path;

        return $this;
    }

    public function getFigure(): ?Figure {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): static {
        $this->figure = $figure;

        return $this;
    }
}
