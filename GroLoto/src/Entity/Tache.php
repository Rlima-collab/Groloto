<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'TACHE')]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(length: 100)]
    private ?string $poste_requis = null;

    #[ORM\Column]
    private ?int $max_personnes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarque = null;

    #[ORM\ManyToOne(targetEntity: Weekend::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(name: "id_weekend", referencedColumnName: "id", nullable: false)]
    private ?Weekend $weekend = null;

    public function getWeekend(): ?Weekend
    {
        return $this->weekend;
    }

    public function setWeekend(?Weekend $weekend): self
    {
        $this->weekend = $weekend;
        return $this;
    }
    #[ORM\JoinColumn(nullable: false, name: 'id_evenement', referencedColumnName: 'id')]
    private ?Evenement $evenement = null;

    // --- GETTERS & SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): self
    {
        $this->debut = $debut;
        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): self
    {
        $this->fin = $fin;
        return $this;
    }

    public function getPosteRequis(): ?string
    {
        return $this->poste_requis;
    }

    public function setPosteRequis(string $poste_requis): self
    {
        $this->poste_requis = $poste_requis;
        return $this;
    }

    public function getMaxPersonnes(): ?int
    {
        return $this->max_personnes;
    }

    public function setMaxPersonnes(int $max_personnes): self
    {
        $this->max_personnes = $max_personnes;
        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): self
    {
        $this->remarque = $remarque;
        return $this;
    }

    // AJOUTÉ : getter pour la relation evenement
    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }
}