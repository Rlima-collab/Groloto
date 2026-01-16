<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "PARAMETRE")]
class Parametre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", unique: true)]
    private string $cle;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $valeur = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCle(): string
    {
        return $this->cle;
    }

    public function setCle(string $cle): self
    {
        $this->cle = $cle;
        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(?string $valeur): self
    {
        $this->valeur = $valeur;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(?\DateTimeInterface $date_modification): self
    {
        $this->date_modification = $date_modification;
        return $this;
    }
}
