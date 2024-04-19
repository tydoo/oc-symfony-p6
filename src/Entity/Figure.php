<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FigureRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Il y a déjà une figure avec ce nom')]
#[ORM\Entity(repositoryClass: FigureRepository::class)]
class Figure {
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
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(
        mappedBy: 'figure',
        targetEntity: Photo::class,
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private Collection $photos;

    /**
     * @var Collection<int, Video>
     */
    #[ORM\OneToMany(
        mappedBy: 'Figure',
        targetEntity: Video::class,
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private Collection $videos;

    public function __construct() {
        $this->photos = new ArrayCollection();
        $this->videos = new ArrayCollection();
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
        $this->createdAt = $this->createdAt ?? new DateTimeImmutable();
        $this->UpdatedAt = $this->UpdatedAt ?? new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate() {
        $this->UpdatedAt = $this->UpdatedAt ?? new DateTimeImmutable();
    }

    public function getSlug(): string {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->name)->lower();
    }

    #[ORM\PreRemove]
    public function onPreRemove() {
        $fileSystem = new Filesystem();
        $fileSystem->remove($_ENV['UPLOAD_DIR'] . $this->getName());
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setFigure($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getFigure() === $this) {
                $photo->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection {
        return $this->videos;
    }

    public function addVideo(Video $video): static {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setFigure($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getFigure() === $this) {
                $video->setFigure(null);
            }
        }

        return $this;
    }

    public function getFeaturedPhoto(): ?Photo {
        $featuredPhoto = null;
        foreach ($this->photos as $photo) {
            if ($photo->isFeatured()) {
                $featuredPhoto = $photo;
                break;
            }
        }

        if ($featuredPhoto === null && !$this->photos->isEmpty()) {
            $featuredPhoto = $this->photos->first();
        }

        return $featuredPhoto;
    }
}
