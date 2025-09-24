<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "HELLOASSO")]
class HelloAsso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id", nullable: true)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $id_externe = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $type_ticket = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_achat = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_import = null;

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

    public function getIdExterne(): ?string
    {
        return $this->id_externe;
    }

    public function setIdExterne(?string $id_externe): self
    {
        $this->id_externe = $id_externe;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getTypeTicket(): ?string
    {
        return $this->type_ticket;
    }

    public function setTypeTicket(?string $type_ticket): self
    {
        $this->type_ticket = $type_ticket;
        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->date_achat;
    }

    public function setDateAchat(?\DateTimeInterface $date_achat): self
    {
        $this->date_achat = $date_achat;
        return $this;
    }

    public function getDateImport(): ?\DateTimeInterface
    {
        return $this->date_import;
    }

    public function setDateImport(?\DateTimeInterface $date_import): self
    {
        $this->date_import = $date_import;
        return $this;
    }
}
