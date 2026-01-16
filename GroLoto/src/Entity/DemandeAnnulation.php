<?php

namespace App\Entity;

use App\Repository\DemandeAnnulationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeAnnulationRepository::class)]
#[ORM\Table(name: 'DEMANDE_ANNULATION')]
class DemandeAnnulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AffectationTache::class)]
    #[ORM\JoinColumn(name: 'id_affectation', nullable: true, onDelete: 'SET NULL')]
    private ?AffectationTache $affectation = null;

    #[ORM\ManyToOne(targetEntity: Benevole::class)]
    #[ORM\JoinColumn(name: 'id_benevole', nullable: false)]
    private ?Benevole $benevole = null;

    #[ORM\Column(name: 'date_demande', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(name: 'statut', length: 20)]
    private ?string $statut = null; // en_attente, acceptee, refusee

    #[ORM\Column(name: 'motif_benevole', type: Types::TEXT, nullable: true)]
    private ?string $motifBenevole = null;

    #[ORM\Column(name: 'tache_titre', length: 255, nullable: true)]
    private ?string $tacheTitre = null;

    #[ORM\Column(name: 'message_admin', type: Types::TEXT, nullable: true)]
    private ?string $messageAdmin = null;

    #[ORM\Column(name: 'date_reponse', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReponse = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_admin_reponse', nullable: true)]
    private ?Utilisateur $adminReponse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAffectation(): ?AffectationTache
    {
        return $this->affectation;
    }

    public function setAffectation(?AffectationTache $affectation): self
    {
        $this->affectation = $affectation;
        return $this;
    }

    public function getBenevole(): ?Benevole
    {
        return $this->benevole;
    }

    public function setBenevole(?Benevole $benevole): self
    {
        $this->benevole = $benevole;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
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

    public function getMotifBenevole(): ?string
    {
        return $this->motifBenevole;
    }

    public function setMotifBenevole(?string $motifBenevole): self
    {
        $this->motifBenevole = $motifBenevole;
        return $this;
    }

    public function getTacheTitre(): ?string
    {
        return $this->tacheTitre;
    }

    public function setTacheTitre(?string $tacheTitre): self
    {
        $this->tacheTitre = $tacheTitre;
        return $this;
    }

    public function getMessageAdmin(): ?string
    {
        return $this->messageAdmin;
    }

    public function setMessageAdmin(?string $messageAdmin): self
    {
        $this->messageAdmin = $messageAdmin;
        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(?\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }

    public function getAdminReponse(): ?Utilisateur
    {
        return $this->adminReponse;
    }

    public function setAdminReponse(?Utilisateur $adminReponse): self
    {
        $this->adminReponse = $adminReponse;
        return $this;
    }
}
