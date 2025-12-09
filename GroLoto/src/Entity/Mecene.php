<?php
namespace App\Entity;

use App\Repository\MeceneRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;

#[ORM\Entity(repositoryClass: MeceneRepository::class)]
#[ORM\Table(name: 'MECENE')]
class Mecene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", nullable: true)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $organisation = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adresse_postale = null;

    public function getId(): ?int { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): self 
    { $this->utilisateur = $utilisateur; return $this; }

    public function getOrganisation(): string { return $this->organisation; }
    public function setOrganisation(string $organisation): self 
    { $this->organisation = $organisation; return $this; }

    public function getSiret(): string { return $this->siret; }
    public function setSiret(string $siret): self 
    { $this->siret = $siret; return $this; }

    public function getAdressePostale(): ?string { return $this->adresse_postale; }
    public function setAdressePostale(?string $adresse_postale): self 
    { $this->adresse_postale = $adresse_postale; return $this; }
}