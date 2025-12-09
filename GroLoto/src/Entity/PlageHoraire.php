<?php

namespace App\Entity;

use App\Repository\PlageHoraireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlageHoraireRepository::class)]
#[ORM\Table(name: 'PLAGE_HORAIRE')]
class PlageHoraire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tache::class, inversedBy: 'plagesHoraires')]
    #[ORM\JoinColumn(name: 'id_tache', referencedColumnName: 'id', nullable: false)]
    private ?Tache $tache = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $jour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxPersonnesPlage = null;

    // ====================
    // GETTERS & SETTERS
    // ====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(?Tache $tache): self
    {
        $this->tache = $tache;
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

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getMaxPersonnesPlage(): ?int
    {
        return $this->maxPersonnesPlage;
    }

    public function setMaxPersonnesPlage(?int $maxPersonnesPlage): self
    {
        $this->maxPersonnesPlage = $maxPersonnesPlage;
        return $this;
    }

    /**
     * Retourne une DateTime complète (jour + heure)
     */
    public function getDebut(): \DateTimeInterface
    {
        if (!$this->jour || !$this->heureDebut) {
            throw new \RuntimeException('Jour et heure de début doivent être définis');
        }

        $debut = clone $this->jour;
        $heure = $this->heureDebut->format('H:i:s');
        $debut->setTime(
            (int)explode(':', $heure)[0],
            (int)explode(':', $heure)[1],
            (int)explode(':', $heure)[2]
        );
        return $debut;
    }

    /**
     * Retourne une DateTime complète (jour + heure fin)
     */
    public function getFin(): \DateTimeInterface
    {
        if (!$this->jour || !$this->heureFin) {
            throw new \RuntimeException('Jour et heure de fin doivent être définis');
        }

        $fin = clone $this->jour;
        $heure = $this->heureFin->format('H:i:s');
        $fin->setTime(
            (int)explode(':', $heure)[0],
            (int)explode(':', $heure)[1],
            (int)explode(':', $heure)[2]
        );
        return $fin;
    }
}
