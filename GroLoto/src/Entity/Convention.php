<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "CONVENTION")]
class Convention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Mecene::class)]
    #[ORM\JoinColumn(name: "id_mecene", referencedColumnName: "id", nullable: false)]
    private ?Mecene $mecene = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $nom_modele = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $url_pdf = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_signature = null;

    #[ORM\Column(type: "string", nullable: false, options: ["default" => "aucune"])]
    private string $methode_signature = 'aucune';

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

    public function getNomModele(): ?string
    {
        return $this->nom_modele;
    }

    public function setNomModele(?string $nom_modele): self
    {
        $this->nom_modele = $nom_modele;
        return $this;
    }

    public function getUrlPdf(): ?string
    {
        return $this->url_pdf;
    }

    public function setUrlPdf(?string $url_pdf): self
    {
        $this->url_pdf = $url_pdf;
        return $this;
    }

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->date_signature;
    }

    public function setDateSignature(?\DateTimeInterface $date_signature): self
    {
        $this->date_signature = $date_signature;
        return $this;
    }

    public function getMethodeSignature(): string
    {
        return $this->methode_signature;
    }

    public function setMethodeSignature(string $methode_signature): self
    {
        $this->methode_signature = $methode_signature;
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
