<?php

namespace App\Entity;

use App\Repository\WeekendRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeekendRepository::class)]
#[ORM\Table(name: "WEEKEND")]
class Weekend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cover_image = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'weekend', targetEntity: Evenement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'weekend', targetEntity: Tache::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $taches;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
        $this->taches = new ArrayCollection();
        $this->date_creation = new \DateTime();
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setWeekend($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            // Set the owning side to null (unless already changed)
            if ($tache->getWeekend() === $this) {
                $tache->setWeekend(null);
            }
        }
        return $this;
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->date_debut; }
    public function setDateDebut(\DateTimeInterface $date_debut): self { $this->date_debut = $date_debut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->date_fin; }
    public function setDateFin(\DateTimeInterface $date_fin): self { $this->date_fin = $date_fin; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->date_creation; }

    public function getCoverImage(): ?string
    {
        return $this->cover_image;
    }

    public function setCoverImage(?string $cover_image): self
    {
        $this->cover_image = $cover_image;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection { return $this->evenements; }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setWeekend($this);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        if ($this->evenements->removeElement($evenement) && $evenement->getWeekend() === $this) {
            $evenement->setWeekend(null);
        }
        return $this;
    }
}