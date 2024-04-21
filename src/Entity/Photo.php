<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PhotoRepository;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Figure $figure = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $featured = false;

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

    public function isFeatured(): ?bool {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static {
        $this->featured = $featured;

        return $this;
    }
}
