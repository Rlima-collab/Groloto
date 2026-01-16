<?php

namespace App\Entity;

use App\Repository\DisponibiliteWeekendRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisponibiliteWeekendRepository::class)]
#[ORM\Table(name: 'DISPONIBILITE_WEEKEND')]
#[ORM\UniqueConstraint(name: 'unique_benevole_weekend_jour', columns: ['id_benevole', 'id_weekend', 'jour'])]
class DisponibiliteWeekend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Benevole::class)]
    #[ORM\JoinColumn(name: 'id_benevole', referencedColumnName: 'id', nullable: false)]
    private ?Benevole $benevole = null;

    #[ORM\ManyToOne(targetEntity: Weekend::class)]
    #[ORM\JoinColumn(name: 'id_weekend', referencedColumnName: 'id', nullable: false)]
    private ?Weekend $weekend = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $jour = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $matin = false;

    #[ORM\Column(name: 'apres_midi', type: 'boolean', options: ['default' => false])]
    private bool $apresMidi = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $soir = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(name: 'date_creation', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWeekend(): ?Weekend
    {
        return $this->weekend;
    }

    public function setWeekend(?Weekend $weekend): self
    {
        $this->weekend = $weekend;
        return $this;
    }

    public function getJour(): ?\DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(\DateTimeInterface $jour): self
    {
        $this->jour = $jour;
        return $this;
    }

    public function isMatin(): bool
    {
        return $this->matin;
    }

    public function setMatin(bool $matin): self
    {
        $this->matin = $matin;
        return $this;
    }

    public function isApresMidi(): bool
    {
        return $this->apresMidi;
    }

    public function setApresMidi(bool $apresMidi): self
    {
        $this->apresMidi = $apresMidi;
        return $this;
    }

    public function isSoir(): bool
    {
        return $this->soir;
    }

    public function setSoir(bool $soir): self
    {
        $this->soir = $soir;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Vérifie si le bénévole est disponible sur au moins un créneau
     */
    public function hasAnyDisponibilite(): bool
    {
        return $this->matin || $this->apresMidi || $this->soir;
    }

    /**
     * Retourne un résumé des créneaux disponibles
     */
    public function getCreneauxResume(): string
    {
        $creneaux = [];
        if ($this->matin) $creneaux[] = 'Matin';
        if ($this->apresMidi) $creneaux[] = 'Après-midi';
        if ($this->soir) $creneaux[] = 'Soir';
        return implode(', ', $creneaux) ?: 'Non disponible';
    }
}
