<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "COMMUNICATION")]
class Communication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id", nullable: true)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: "string")]
    private string $titre;

    #[ORM\Column(type: "string", nullable: true, options: ["default" => "post"])]
    private ?string $type = 'post';

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_prevue = null;

    #[ORM\Column(type: "string", nullable: true, options: ["default" => "brouillon"])]
    private ?string $statut = 'brouillon';

    #[ORM\Column(type: "float", nullable: true, options: ["default" => 0.0])]
    private ?float $budget = 0.0;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDatePrevue(): ?\DateTimeInterface
    {
        return $this->date_prevue;
    }

    public function setDatePrevue(?\DateTimeInterface $date): self
    {
        $this->date_prevue = $date;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): self
    {
        $this->budget = $budget;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date): self
    {
        $this->date_creation = $date;
        return $this;
    }
}
