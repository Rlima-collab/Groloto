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

    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private ?int $quantite = 0;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $unite = null;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private ?int $seuil = 0;

    #[ORM\Column(type: "float", options: ["default" => 0.0])]
    private ?float $valeur_unitaire = 0.0;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $derniere_modif = null;

    #[ORM\Column(length: 20, options: ["default" => "achat"])]
    private ?string $source = 'achat';

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_retour = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preteur = null;

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
        if ($categorie !== null && !in_array($categorie, ['bar', 'resto', 'deco', 'autre'])) {
            throw new \InvalidArgumentException("La catégorie doit être l'une des suivantes : bar, resto, deco, autre.");
        }
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

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->date_retour;
    }

    public function setDateRetour(?\DateTimeInterface $date_retour): self
    {
        $this->date_retour = $date_retour;
        return $this;
    }

    public function getPreteur(): ?string
    {
        return $this->preteur;
    }

    public function setPreteur(?string $preteur): self
    {
        $this->preteur = $preteur;
        return $this;
    }

    public function getValeurUnitaire(): ?float
    {
        return $this->valeur_unitaire;
    }

    public function setValeurUnitaire(float $valeur_unitaire): self
    {
        $this->valeur_unitaire = $valeur_unitaire;
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

    public function getDerniereModif(): ?\DateTimeInterface
    {
        return $this->derniere_modif;
    }

    public function setDerniereModif(?\DateTimeInterface $derniere_modif): self
    {
        $this->derniere_modif = $derniere_modif;
        return $this;
    }
}