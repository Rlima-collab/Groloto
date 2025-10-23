<?php
namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date_creation = null;

    // ✅ SANS inversedBy - ça suffit !
    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Tache::class)]
    private Collection $taches;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
        $this->date_creation = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self 
    { 
        $this->nom = $nom; 
        return $this; 
    }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self 
    { 
        $this->description = $description; 
        return $this; 
    }

    public function getDateDebut(): ?\DateTimeInterface { return $this->date_debut; }
    public function setDateDebut(?\DateTimeInterface $date_debut): self 
    { 
        $this->date_debut = $date_debut; 
        return $this; 
    }

    public function getDateFin(): ?\DateTimeInterface { return $this->date_fin; }
    public function setDateFin(?\DateTimeInterface $date_fin): self 
    { 
        $this->date_fin = $date_fin; 
        return $this; 
    }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): self 
    { 
        $this->lieu = $lieu; 
        return $this; 
    }

    public function getDateCreation(): ?\DateTimeInterface { return $this->date_creation; }
    public function setDateCreation(?\DateTimeInterface $date_creation): self 
    { 
        $this->date_creation = $date_creation; 
        return $this; 
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
            $tache->setEvenement($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            if ($tache->getEvenement() === $this) {
                $tache->setEvenement(null);
            }
        }
        return $this;
    }
}