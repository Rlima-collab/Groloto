<?php
namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
#[ORM\Table(name: 'CONTACT_MESSAGE')]
class ContactMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $destinataire = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(type: 'datetime')] // ← DATETIME
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private bool $lu = false;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'repondu_par_id', referencedColumnName: 'id', nullable: true)]
    private ?Utilisateur $reponduPar = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reponduLe = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $parent_id = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $cloturee = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $masqueePour = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // === Getters & Setters ===
    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getDestinataire(): ?string { return $this->destinataire; }
    public function setDestinataire(?string $destinataire): self { $this->destinataire = $destinataire; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self 
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isLu(): bool { return $this->lu; }
    public function setLu(bool $lu): self { $this->lu = $lu; return $this; }

    public function getReponduPar(): ?Utilisateur { return $this->reponduPar; }
    public function setReponduPar(?Utilisateur $reponduPar): self { $this->reponduPar = $reponduPar; return $this; }

    public function getReponse(): ?string { return $this->reponse; }
    public function setReponse(?string $reponse): self { $this->reponse = $reponse; return $this; }

    public function getReponduLe(): ?\DateTimeInterface { return $this->reponduLe; }
    public function setReponduLe(?\DateTimeInterface $reponduLe): self { $this->reponduLe = $reponduLe; return $this; }

    public function getParentId(): ?int { return $this->parent_id; }
    public function setParentId(?int $parent_id): self { $this->parent_id = $parent_id; return $this; }

    public function isCloturee(): bool { return $this->cloturee; }
    public function setCloturee(bool $cloturee): self { $this->cloturee = $cloturee; return $this; }

    public function getMasqueePour(): ?string { return $this->masqueePour; }
    public function setMasqueePour(?string $masqueePour): self { $this->masqueePour = $masqueePour; return $this; }
    
    public function isMasqueePour(string $email): bool
    {
        if (!$this->masqueePour) return false;
        $emails = explode(',', $this->masqueePour);
        return in_array($email, $emails);
    }
    
    public function masquerPour(string $email): self
    {
        $emails = $this->masqueePour ? explode(',', $this->masqueePour) : [];
        if (!in_array($email, $emails)) {
            $emails[] = $email;
            $this->masqueePour = implode(',', $emails);
        }
        return $this;
    }
}