<?php
namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: "TACHE")]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id", nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: AffectationTache::class)]
    private Collection $affectations;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $poste_requis = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(type: "integer")]
    private ?int $max_personnes = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    public function __construct()
    {
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getEvenement(): ?Evenement { return $this->evenement; }
    public function setEvenement(?Evenement $evenement): self 
    { $this->evenement = $evenement; return $this; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): self 
    { $this->titre = $titre; return $this; }

    public function getPosteRequis(): ?string { return $this->poste_requis; }
    public function setPosteRequis(?string $poste_requis): self 
    { $this->poste_requis = $poste_requis; return $this; }

    public function getDebut(): ?\DateTimeInterface { return $this->debut; }
    public function setDebut(\DateTimeInterface $debut): self 
    { $this->debut = $debut; return $this; }

    public function getFin(): ?\DateTimeInterface { return $this->fin; }
    public function setFin(\DateTimeInterface $fin): self 
    { $this->fin = $fin; return $this; }

    public function getMaxPersonnes(): ?int { return $this->max_personnes; }
    public function setMaxPersonnes(int $max_personnes): self 
    { $this->max_personnes = $max_personnes; return $this; }

    public function getRemarque(): ?string { return $this->remarque; }
    public function setRemarque(?string $remarque): self 
    { $this->remarque = $remarque; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->date_creation; }
    public function setDateCreation(?\DateTimeInterface $date_creation): self 
    { $this->date_creation = $date_creation; return $this; }

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
            if ($affectation->getTache() === $this) {
                $affectation->setTache(null);
            }
        }
        return $this;
    }
}