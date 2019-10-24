<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TripRepository")
 */
class Trip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(min = 4, max = 100, minMessage = "Le nom de la sortie doit contenir au moins {{ limit }} caractères",maxMessage = "Le nom de la sortie ne peut pas excèder {{ limit }} caracètres")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Assert\GreaterThan("today", message="Votre date de sortie doit être supérieur à aujourd'hui")
     */
    private $startDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Range(min = 15, max = 360, minMessage = "Votre sortie doit faire au moins {{ limit }} minutes",maxMessage = "Votre sortie doit faire au plus {{ limit }} minutes")
     */
    private $duration;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Assert\Expression("this.getStartDate() > this.getDeadlineDate()", message="La date limite d'inscription doit être inférieur à la date de la sortie")
     */
    private $deadlineDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Range(min = 2, max = 50, minMessage = "Vous ne pouvez pas organiser une sortie pour vous seuelement",maxMessage = "Votre sortie ne peut excéder {{ limit }} personnes")
     */
    private $maxRegistrationNumber;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParticipantArea", inversedBy="trips")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank
     */
    private $participantArea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TripStatus")
     * @ORM\JoinColumn(nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Participant", inversedBy="organizedTrips")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $organizer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Participant", mappedBy="participatingTrips")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TripPlace", inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->setStartDate(new \DateTime());
        $this->setDeadlineDate(new \DateTime());
        $this->setDuration(15);
        $this->setMaxRegistrationNumber(2);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDeadlineDate(): ?\DateTimeInterface
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(\DateTimeInterface $deadlineDate): self
    {
        $this->deadlineDate = $deadlineDate;

        return $this;
    }

    public function getMaxRegistrationNumber(): ?int
    {
        return $this->maxRegistrationNumber;
    }

    public function setMaxRegistrationNumber(int $maxRegistrationNumber): self
    {
        $this->maxRegistrationNumber = $maxRegistrationNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getStatus(): ?TripStatus
    {
        return $this->status;
    }

    public function setStatus(?TripStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrganizer(): ?Participant
    {
        return $this->organizer;
    }

    public function setOrganizer(?Participant $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addParticipatingTrip($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            $participant->removeParticipatingTrip($this);
        }

        return $this;
    }

    public function getPlace(): ?TripPlace
    {
        return $this->place;
    }

    public function setPlace(?TripPlace $place): self
    {
        $this->place = $place;

        return $this;
    }
}
