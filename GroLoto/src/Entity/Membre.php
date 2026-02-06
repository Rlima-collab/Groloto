<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[ORM\Table(name: 'MEMBRE')]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $numero_billet = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $tarif = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $date_creation = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $date_seance = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $montant_tarif = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $code_promo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $montant_code_promo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getNumeroBillet(): ?string
    {
        return $this->numero_billet;
    }

    public function setNumeroBillet(?string $numero_billet): self
    {
        $this->numero_billet = $numero_billet;
        return $this;
    }

    public function getTarif(): ?string
    {
        return $this->tarif;
    }

    public function setTarif(?string $tarif): self
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function getDateCreation(): ?DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getDateSeance(): ?DateTime
    {
        return $this->date_seance;
    }

    public function setDateSeance(?DateTime $date_seance): self
    {
        $this->date_seance = $date_seance;
        return $this;
    }

    public function getMontantTarif(): ?int
    {
        return $this->montant_tarif;
    }

    public function setMontantTarif(?int $montant_tarif): self
    {
        $this->montant_tarif = $montant_tarif;
        return $this;
    }

    public function getCodePromo(): ?string
    {
        return $this->code_promo;
    }

    public function setCodePromo(?string $code_promo): self
    {
        $this->code_promo = $code_promo;
        return $this;
    }

    public function getMontantCodePromo(): ?string
    {
        return $this->montant_code_promo;
    }

    public function setMontantCodePromo(?string $montant_code_promo): self
    {
        $this->montant_code_promo = $montant_code_promo;
        return $this;
    }
}
