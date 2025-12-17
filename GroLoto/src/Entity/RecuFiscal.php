<?php

namespace App\Entity;

use App\Repository\RecuFiscalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecuFiscalRepository::class)]
#[ORM\Table(name: 'RECU_FISCAL')]
class RecuFiscal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $destinataire = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // 'mecene' ou 'benevole'

    #[ORM\Column(length: 255)]
    private ?string $fichier = null; // chemin vers le fichier

    #[ORM\Column(type: 'integer')]
    private ?int $annee = null; // année fiscale

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'envoye_par_id', nullable: false)]
    private ?Utilisateur $envoyePar = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDestinataire(): ?Utilisateur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateur $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEnvoyePar(): ?Utilisateur
    {
        return $this->envoyePar;
    }

    public function setEnvoyePar(?Utilisateur $envoyePar): static
    {
        $this->envoyePar = $envoyePar;

        return $this;
    }
}