<?php
// src/Entity/Utilisateur.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="UTILISATEUR")
 */
class Utilisateur
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Role") 
     *  @ORM\JoinColumn(name="id_role", referencedColumnName="id", nullable=false)
     */
    private $role;

    /** @ORM\Column(type="string", unique=true) */
    private $email;

    /** @ORM\Column(type="string", nullable=true) */
    private $mot_de_passe;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenom;

    /** @ORM\Column(type="string", nullable=true) */
    private $nom;

    /** @ORM\Column(type="string", nullable=true) */
    private $telephone;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_modification;

    // getters & setters
}
