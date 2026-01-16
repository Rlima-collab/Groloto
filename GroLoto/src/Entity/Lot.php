<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "LOT")]
class Lot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Mecene::class, inversedBy: 'lots')]
    #[ORM\JoinColumn(name: "id_mecene", referencedColumnName: "id", nullable: true)]
    private ?Mecene $mecene = null;

    #[ORM\ManyToOne(targetEntity: Weekend::class)]
    #[ORM\JoinColumn(name: "id_weekend", referencedColumnName: "id", nullable: true)]
    private ?Weekend $weekend = null;

    #[ORM\Column(type: "string")]
    private string $titre;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "integer")]
    private int $quantite = 1;

    #[ORM\Column(type: "float")]
    private float $valeur_estimee = 0.0;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMecene(): ?Mecene
    {
        return $this->mecene;
    }

    public function setMecene(?Mecene $mecene): self
    {
        $this->mecene = $mecene;
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    public function getValeurEstimee(): float
    {
        return $this->valeur_estimee;
    }

    public function setValeurEstimee(float $valeur): self
    {
        $this->valeur_estimee = $valeur;
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
}
