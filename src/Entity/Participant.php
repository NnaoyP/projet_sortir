<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
 * @UniqueEntity(fields={"email"}, message="Cette adresse mail est déjà utilisée")
 */
class Participant implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParticipantArea", inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participantArea;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trip", mappedBy="organizer")
     */
    private $organizedTrips;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Trip", inversedBy="participants")
     */
    private $participatingTrips;

    public function __construct()
    {
        $this->isActive = 1;
        $this->setRoles(['ROLE_USER']);
        $this->organizedTrips = new ArrayCollection();
        $this->participatingTrips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getParticipantArea(): ?ParticipantArea
    {
        return $this->participantArea;
    }

    public function setParticipantArea(?ParticipantArea $participantArea): self
    {
        $this->participantArea = $participantArea;

        return $this;
    }

    /**
     * @return Collection|Trip[]
     */
    public function getOrganizedTrips(): Collection
    {
        return $this->organizedTrips;
    }

    public function addOrganizedTrip(Trip $organizedTrip): self
    {
        if (!$this->organizedTrips->contains($organizedTrip)) {
            $this->organizedTrips[] = $organizedTrip;
            $organizedTrip->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizedTrip(Trip $organizedTrip): self
    {
        if ($this->organizedTrips->contains($organizedTrip)) {
            $this->organizedTrips->removeElement($organizedTrip);
            // set the owning side to null (unless already changed)
            if ($organizedTrip->getOrganizer() === $this) {
                $organizedTrip->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Trip[]
     */
    public function getParticipatingTrips(): Collection
    {
        return $this->participatingTrips;
    }

    public function addParticipatingTrip(Trip $participatingTrip): self
    {
        if (!$this->participatingTrips->contains($participatingTrip)) {
            $this->participatingTrips[] = $participatingTrip;
        }

        return $this;
    }

    public function removeParticipatingTrip(Trip $participatingTrip): self
    {
        if ($this->participatingTrips->contains($participatingTrip)) {
            $this->participatingTrips->removeElement($participatingTrip);
        }

        return $this;
    }
}
