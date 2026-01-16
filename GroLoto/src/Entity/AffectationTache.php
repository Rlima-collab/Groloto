<?php
namespace App\Entity;

use App\Repository\AffectationTacheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AffectationTacheRepository::class)]
#[ORM\Table(name: "AFFECTATION_TACHE")]
class AffectationTache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tache::class, inversedBy: 'affectations')]
    #[ORM\JoinColumn(name: "id_tache", referencedColumnName: "id", nullable: false)]
    private ?Tache $tache = null;

    #[ORM\ManyToOne(targetEntity: Benevole::class)]
    #[ORM\JoinColumn(name: "id_benevole", referencedColumnName: "id", nullable: false)]
    private ?Benevole $benevole = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id")]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date_affectation = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remarque = null;

    // Getters/Setters
    public function getId(): ?int { return $this->id; }

    public function getTache(): ?Tache { return $this->tache; }
    public function setTache(?Tache $tache): self 
    { 
        $this->tache = $tache; 
        return $this; 
    }

    public function getBenevole(): ?Benevole { return $this->benevole; }
    public function setBenevole(?Benevole $benevole): self 
    { 
        $this->benevole = $benevole; 
        return $this; 
    }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): self 
    { 
        $this->utilisateur = $utilisateur; 
        return $this; 
    }

    public function getDateAffectation(): ?\DateTimeInterface { return $this->date_affectation; }
    public function setDateAffectation(\DateTimeInterface $date_affectation): self 
    { 
        $this->date_affectation = $date_affectation; 
        return $this; 
    }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self 
    { 
        $this->statut = $statut; 
        return $this; 
    }

    public function getRemarque(): ?string { return $this->remarque; }
    public function setRemarque(?string $remarque): self 
    { 
        $this->remarque = $remarque; 
        return $this; 
    }
}