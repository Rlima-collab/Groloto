<?php
namespace App\Entity;

use App\Repository\DemandeTacheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeTacheRepository::class)]
#[ORM\Table(name: "DEMANDE_TACHE")]
class DemandeTache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tache::class)]
    #[ORM\JoinColumn(name: "id_tache", referencedColumnName: "id", nullable: false)]
    private ?Tache $tache = null;

    #[ORM\ManyToOne(targetEntity: Benevole::class)]
    #[ORM\JoinColumn(name: "id_benevole", referencedColumnName: "id", nullable: false)]
    private ?Benevole $benevole = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date_demande = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $statut = 'en_attente'; // en_attente, acceptee, refusee

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $message_benevole = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $message_admin = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_reponse = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_admin_reponse", referencedColumnName: "id", nullable: true)]
    private ?Utilisateur $admin_reponse = null;

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

    public function getDateDemande(): ?\DateTimeInterface { return $this->date_demande; }
    public function setDateDemande(\DateTimeInterface $date_demande): self 
    { 
        $this->date_demande = $date_demande; 
        return $this; 
    }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self 
    { 
        $this->statut = $statut; 
        return $this; 
    }

    public function getMessageBenevole(): ?string { return $this->message_benevole; }
    public function setMessageBenevole(?string $message_benevole): self 
    { 
        $this->message_benevole = $message_benevole; 
        return $this; 
    }

    public function getMessageAdmin(): ?string { return $this->message_admin; }
    public function setMessageAdmin(?string $message_admin): self 
    { 
        $this->message_admin = $message_admin; 
        return $this; 
    }

    public function getDateReponse(): ?\DateTimeInterface { return $this->date_reponse; }
    public function setDateReponse(?\DateTimeInterface $date_reponse): self 
    { 
        $this->date_reponse = $date_reponse; 
        return $this; 
    }

    public function getAdminReponse(): ?Utilisateur { return $this->admin_reponse; }
    public function setAdminReponse(?Utilisateur $admin_reponse): self 
    { 
        $this->admin_reponse = $admin_reponse; 
        return $this; 
    }
}
