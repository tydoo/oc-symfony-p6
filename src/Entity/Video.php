<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Figure $figure = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $path = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getFigure(): ?Figure {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): static {
        $this->figure = $figure;

        return $this;
    }

    public function getPath(): ?string {
        return html_entity_decode($this->path);
    }

    public function setPath(string $path): static {
        $this->path = $path;

        return $this;
    }
}
