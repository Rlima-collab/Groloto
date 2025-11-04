<?php

namespace App\Entity;

use App\Repository\InscriptionMeceneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionMeceneRepository::class)]
#[ORM\Table(name: 'INSCRIPTION_MECENE')]
class InscriptionMecene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Mecene::class)]
    #[ORM\JoinColumn(name: 'id_mecene', referencedColumnName: 'id', nullable: false)]
    private ?Mecene $mecene = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: 'id_evenement', referencedColumnName: 'id', nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: 'text')]
    private ?string $description_don = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montant_estime = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'en_attente';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_inscription = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remarques = null;

    // --- GETTERS & SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMecene(): ?Mecene
    {
        return $this->mecene;
    }

    public function setMecene(?Mecene $mecene): self
    {
        $this->mecene = $mecene;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getDescriptionDon(): ?string
    {
        return $this->description_don;
    }

    public function setDescriptionDon(string $description_don): self
    {
        $this->description_don = $description_don;
        return $this;
    }

    public function getMontantEstime(): ?float
    {
        return $this->montant_estime;
    }

    public function setMontantEstime(?float $montant_estime): self
    {
        $this->montant_estime = $montant_estime;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): self
    {
        $this->remarques = $remarques;
        return $this;
    }
}