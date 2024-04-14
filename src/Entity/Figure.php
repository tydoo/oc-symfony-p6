<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FigureRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Il y a déjà une figure avec ce nom')]
#[ORM\Entity(repositoryClass: FigureRepository::class)]
class Figure {

    public function __construct(private readonly SluggerInterface $slugger) {
        $this->files = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'figures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $UpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'figures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(mappedBy: 'figure', targetEntity: File::class, orphanRemoval: true)]
    private Collection $files;

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

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): static {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category {
        return $this->category;
    }

    public function setCategory(?Category $category): static {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable {
        return $this->createdAt;
    }

    /**
     * Do not call this method directly. It is only used by Doctrine events.
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): static {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable {
        return $this->UpdatedAt;
    }

    /**
     * Do not call this method directly. It is only used by Doctrine events.
     */
    public function setUpdatedAt(DateTimeImmutable $UpdatedAt): static {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getCreatedBy(): ?User {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?User {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist() {
        $this->createdAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate() {
        $this->UpdatedAt = new DateTimeImmutable();
    }

    public function getSlug(): string {
        return $this->slugger->slug($this->name)->lower();
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection {
        return $this->files;
    }

    public function addFile(File $file): static {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setFigure($this);
        }

        return $this;
    }

    public function removeFile(File $file): static {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getFigure() === $this) {
                $file->setFigure(null);
            }
        }

        return $this;
    }
}
