<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
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
     * @ORM\Column(type="string", length=100)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Trip", mappedBy="participants")
     */
    private $participatingTrips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trip", mappedBy="organizers")
     */
    private $organizedTrips;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParticipantArea", inversedBy="participants")
     * @ORM\JoinColumn(nullable=true)
     */
    private $participantArea;

    public function __construct()
    {
        $this->participatingTrips = new ArrayCollection();
        $this->organizedTrips = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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
            $participatingTrip->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatingTrip(Trip $participatingTrip): self
    {
        if ($this->participatingTrips->contains($participatingTrip)) {
            $this->participatingTrips->removeElement($participatingTrip);
            $participatingTrip->removeParticipant($this);
        }

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
            $organizedTrip->setOrganizers($this);
        }

        return $this;
    }

    public function removeOrganizedTrip(Trip $organizedTrip): self
    {
        if ($this->organizedTrips->contains($organizedTrip)) {
            $this->organizedTrips->removeElement($organizedTrip);
            // set the owning side to null (unless already changed)
            if ($organizedTrip->getOrganizers() === $this) {
                $organizedTrip->setOrganizers(null);
            }
        }

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
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        // TODO: Implement getRoles() method.
        return ['ROLE_USER'];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.
        return $this->getEmail();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
