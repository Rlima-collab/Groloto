<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "MECENE")]
class Mecene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", nullable: true)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $organisation = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $nom_contact = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $email_contact = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $telephone_contact = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $adresse = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getOrganisation(): ?string
    {
        return $this->organisation;
    }

    public function setOrganisation(?string $organisation): self
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function getNomContact(): ?string
    {
        return $this->nom_contact;
    }

    public function setNomContact(?string $nom_contact): self
    {
        $this->nom_contact = $nom_contact;
        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->email_contact;
    }

    public function setEmailContact(?string $email_contact): self
    {
        $this->email_contact = $email_contact;
        return $this;
    }

    public function getTelephoneContact(): ?string
    {
        return $this->telephone_contact;
    }

    public function setTelephoneContact(?string $telephone_contact): self
    {
        $this->telephone_contact = $telephone_contact;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }
}
