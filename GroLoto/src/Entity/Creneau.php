<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "CRENEAU")]
class Creneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id", nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: "string")]
    private string $titre;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $poste_requis = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $debut;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $fin;

    #[ORM\Column(type: "integer")]
    private int $max_personnes = 1;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getPosteRequis(): ?string
    {
        return $this->poste_requis;
    }

    public function setPosteRequis(?string $poste): self
    {
        $this->poste_requis = $poste;
        return $this;
    }

    public function getDebut(): \DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): self
    {
        $this->debut = $debut;
        return $this;
    }

    public function getFin(): \DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): self
    {
        $this->fin = $fin;
        return $this;
    }

    public function getMaxPersonnes(): int
    {
        return $this->max_personnes;
    }

    public function setMaxPersonnes(int $max): self
    {
        $this->max_personnes = $max;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date): self
    {
        $this->date_creation = $date;
        return $this;
    }
}
