<?php
namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Mecene;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: "UTILISATEUR")]
#[UniqueEntity(fields: ["email"], message: "Cette adresse email est déjà utilisée")]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: "id_role", referencedColumnName: "id", nullable: false)]
    private ?Role $role = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $mot_de_passe = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\OneToMany(targetEntity: Mecene::class, mappedBy: 'utilisateur')]
    private Collection $mecenes;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->date_modification = new \DateTime();
        $this->mecenes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
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

    public function getMotDePasse(): ?string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): self
    {
        $this->mot_de_passe = $mot_de_passe;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(\DateTimeInterface $date_modification): self
    {
        $this->date_modification = $date_modification;
        return $this;
    }

    // UserInterface and PasswordAuthenticatedUserInterface methods
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->role) {
            switch ($this->role->getNom()) {
                case 'admin':
                    $roles[] = 'ROLE_ADMIN';
                    break;
                case 'benevole':
                    $roles[] = 'ROLE_BENEVOLE';
                    break;
                case 'mecene':
                    $roles[] = 'ROLE_MECENE';
                    break;
                default:
                    $roles[] = 'ROLE_USER';
            }
        }
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return (string) $this->mot_de_passe;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // No sensitive temporary data to erase in this implementation
        // If you store temporary plaintext passwords or other sensitive data, clear them here
    }

    /** @return Collection<int, Mecene> */
    public function getMecenes(): Collection
    {
        return $this->mecenes;
    }

    public function addMecene(Mecene $mecene): self
    {
        if (!$this->mecenes->contains($mecene)) {
            $this->mecenes[] = $mecene;
            $mecene->setUtilisateur($this);
        }

        return $this;
    }

    public function removeMecene(Mecene $mecene): self
    {
        if ($this->mecenes->removeElement($mecene)) {
            // set the owning side to null (unless already changed)
            if ($mecene->getUtilisateur() === $this) {
                $mecene->setUtilisateur(null);
            }
        }

        return $this;
    }
}