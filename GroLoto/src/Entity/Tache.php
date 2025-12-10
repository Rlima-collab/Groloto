<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxPersonnes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posteRequis = null;

    #[ORM\ManyToOne(targetEntity: Weekend::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(name: 'id_weekend', referencedColumnName: 'id', nullable: false)]
    private ?Weekend $weekend = null;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: PlageHoraire::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $plagesHoraires;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: AffectationTache::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $affectations;

    public function __construct()
    {
        $this->plagesHoraires = new ArrayCollection();
        $this->affectations = new ArrayCollection();
    }

    // ====================
    // GETTERS & SETTERS
    // ====================

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

    public function setDebut(?\DateTimeInterface $debut): self
    {
        $this->debut = $debut;
        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(?\DateTimeInterface $fin): self
    {
        $this->fin = $fin;
        return $this;
    }

    public function getMaxPersonnes(): ?int
    {
        return $this->maxPersonnes;
    }

    public function setMaxPersonnes(?int $maxPersonnes): self
    {
        $this->maxPersonnes = $maxPersonnes;
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

    public function getPosteRequis(): ?string
    {
        return $this->posteRequis;
    }

    public function setPosteRequis(?string $posteRequis): self
    {
        $this->posteRequis = $posteRequis;
        return $this;
    }

    public function getWeekend(): ?Weekend
    {
        return $this->weekend;
    }

    public function setWeekend(Weekend $weekend): self
    {
        $this->weekend = $weekend;
        return $this;
    }

    /**
     * @return Collection<int, PlageHoraire>
     */
    public function getPlagesHoraires(): Collection
    {
        return $this->plagesHoraires;
    }

    public function addPlageHoraire(PlageHoraire $plageHoraire): self
    {
        if (!$this->plagesHoraires->contains($plageHoraire)) {
            $this->plagesHoraires->add($plageHoraire);
            $plageHoraire->setTache($this);
        }

        return $this;
    }

    public function removePlageHoraire(PlageHoraire $plageHoraire): self
    {
        if ($this->plagesHoraires->removeElement($plageHoraire)) {
            if ($plageHoraire->getTache() === $this) {
                $plageHoraire->setTache(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AffectationTache>
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(AffectationTache $affectation): self
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setTache($this);
        }

        return $this;
    }

    public function removeAffectation(AffectationTache $affectation): self
    {
        if ($this->affectations->removeElement($affectation)) {
            // set the owning side to null (unless already changed)
            if ($affectation->getTache() === $this) {
                $affectation->setTache(null);
            }
        }

        return $this;
    }
}
