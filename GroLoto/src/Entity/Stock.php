<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "STOCK")]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", nullable: false)]
    private string $nom;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: "integer")]
    private int $quantite = 0;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $unite = null;

    #[ORM\Column(type: "integer")]
    private int $seuil = 0;

    #[ORM\Column(type: "float", nullable: false, options: ["default" => 0])]
    private float $valeur_unitaire = 0;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $derniere_modif = null;

    // === Getters & setters ===

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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }

    public function getSeuil(): ?int
    {
        return $this->seuil;
    }

    public function setSeuil(int $seuil): self
    {
        $this->seuil = $seuil;
        return $this;
    }

    public function getValeurUnitaire(): float
    {
        return $this->valeur_unitaire;
    }

    public function setValeurUnitaire(float $valeur): self
    {
        $this->valeur_unitaire = $valeur;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDerniereModif(): ?\DateTimeInterface
    {
        return $this->derniere_modif;
    }

    public function setDerniereModif(?\DateTimeInterface $date): self
    {
        $this->derniere_modif = $date;
        return $this;
    }
}
