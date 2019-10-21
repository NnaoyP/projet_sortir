<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadlineDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxRegistrationNumber;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Participant", inversedBy="participatingTrips")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Participant", inversedBy="organizedTrips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organizers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParticipantArea", inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participantArea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TripStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TripPlace", inversedBy="trips")
     */
    private $places;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

    public function getOrganizers(): ?Participant
    {
        return $this->organizers;
    }

    public function setOrganizers(?Participant $organizers): self
    {
        $this->organizers = $organizers;

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

    public function getPlaces(): ?TripPlace
    {
        return $this->places;
    }

    public function setPlaces(?TripPlace $places): self
    {
        $this->places = $places;

        return $this;
    }

}
