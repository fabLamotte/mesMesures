<?php

namespace App\Entity;

use App\Repository\InscriptionMesureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InscriptionMesureRepository::class)
 */
class InscriptionMesure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $cm;

    /**
     * @ORM\ManyToOne(targetEntity=Mesures::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mesures;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCm(): ?int
    {
        return $this->cm;
    }

    public function setCm(int $cm): self
    {
        $this->cm = $cm;

        return $this;
    }

    public function getMesures(): ?Mesures
    {
        return $this->mesures;
    }

    public function setMesures(?Mesures $mesures): self
    {
        $this->mesures = $mesures;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
