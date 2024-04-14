<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Figure>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Figure::class)]
    private Collection $figures;

    public function __construct() {
        $this->figures = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Figure>
     */
    public function getFigures(): Collection {
        return $this->figures;
    }

    public function addFigure(Figure $figure): static {
        if (!$this->figures->contains($figure)) {
            $this->figures->add($figure);
            $figure->setCategory($this);
        }

        return $this;
    }

    public function removeFigure(Figure $figure): static {
        if ($this->figures->removeElement($figure)) {
            // set the owning side to null (unless already changed)
            if ($figure->getCategory() === $this) {
                $figure->setCategory(null);
            }
        }

        return $this;
    }
}
