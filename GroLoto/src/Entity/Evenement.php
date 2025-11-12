<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: "EVENEMENT")]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: "time", nullable: true)]
    private ?\DateTimeInterface $heure_debut = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $duree_minutes = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(targetEntity: Weekend::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: "id_weekend", referencedColumnName: "id", nullable: true)]
    private ?Weekend $weekend = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Tache::class, orphanRemoval: true)]
    private Collection $taches;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
        $this->date_creation = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->date_debut; }
    public function setDateDebut(?\DateTimeInterface $date_debut): self { $this->date_debut = $date_debut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->date_fin; }
    public function setDateFin(?\DateTimeInterface $date_fin): self { $this->date_fin = $date_fin; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): self { $this->lieu = $lieu; return $this; }

    public function getHeureDebut(): ?\DateTimeInterface { return $this->heure_debut; }
    public function setHeureDebut(?\DateTimeInterface $heure_debut): self { $this->heure_debut = $heure_debut; return $this; }

    public function getDureeMinutes(): ?int { return $this->duree_minutes; }
    public function setDureeMinutes(?int $duree_minutes): self { $this->duree_minutes = $duree_minutes; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }

    /**
     * Calcule l'heure de fin à partir de l'heure de début et de la durée
     */
    public function getHeureFin(): ?\DateTimeInterface
    {
        if ($this->heure_debut && $this->duree_minutes) {
            $heureFin = clone $this->heure_debut;
            $heureFin->modify('+' . $this->duree_minutes . ' minutes');
            return $heureFin;
        }
        return null;
    }

    public function getDateCreation(): ?\DateTimeInterface { return $this->date_creation; }

    public function getWeekend(): ?Weekend { return $this->weekend; }
    public function setWeekend(?Weekend $weekend): self
    {
        $this->weekend = $weekend;
        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection { return $this->taches; }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setEvenement($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache) && $tache->getEvenement() === $this) {
            $tache->setEvenement(null);
        }
        return $this;
    }
}