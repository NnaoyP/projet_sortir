<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TripStatusRepository")
 */
class TripStatus
{
    const CREATION = 1; //Sauvegarde
    const OPEN = 2; //Ouverte
    const FULL = 3; //clôturée
    const CLOSED = 4; //Archivée
    const DONE = 5; //Clôturé
    const CANCELED = 6; //Anullée
    const RUNNING = 7; //En cours
    const FINISHED = 8; //Terminée

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
}
