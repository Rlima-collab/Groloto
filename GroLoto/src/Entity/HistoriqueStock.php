<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "HISTORIQUE_STOCK")]
class HistoriqueStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Stock::class)]
    #[ORM\JoinColumn(name: "id_stock", referencedColumnName: "id", nullable: false)]
    private ?Stock $stock = null;

    #[ORM\Column(type: "string")]
    private string $type_changement;

    #[ORM\Column(type: "integer")]
    private int $quantite;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $raison = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id", nullable: true)]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", nullable: true)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function getTypeChangement(): string
    {
        return $this->type_changement;
    }

    public function setTypeChangement(string $type_changement): self
    {
        $this->type_changement = $type_changement;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(?string $raison): self
    {
        $this->raison = $raison;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function isEntree(): bool
    {
        return $this->type_changement === 'entree';
    }

    public function isSortie(): bool
    {
        return $this->type_changement === 'sortie';
    }

}
