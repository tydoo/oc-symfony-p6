<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['username'], message: 'Il y a déjà un compte avec ce nom d\'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Il y a déjà un compte avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forgotPasswordToken = null;

    /**
     * @var Collection<int, Figure>
     */
    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Figure::class)]
    private Collection $figures;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct() {
        $this->figures = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(string $username): static {
        $this->username = strtolower($username);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): static {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): static {
        $this->email = strtolower($email);

        return $this;
    }

    public function getPhoto(): ?string {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static {
        $this->photo = $photo;

        return $this;
    }

    public function isVerified(): bool {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getForgotPasswordToken(): ?string {
        return $this->forgotPasswordToken;
    }

    public function setForgotPasswordToken(?string $forgotPasswordToken): static {
        $this->forgotPasswordToken = $forgotPasswordToken;

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
            $figure->setCreatedBy($this);
        }

        return $this;
    }

    public function removeFigure(Figure $figure): static {
        if ($this->figures->removeElement($figure)) {
            // set the owning side to null (unless already changed)
            if ($figure->getCreatedBy() === $this) {
                $figure->setCreatedBy(null);
            }
        }

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

    #[ORM\PrePersist]
    public function onPrePersist() {
        $this->createdAt = $this->createdAt ?? new DateTimeImmutable();
    }
}
