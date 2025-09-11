<?php
// src/Entity/Mecene.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="MECENE")
 */
class Mecene
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\OneToOne(targetEntity="Utilisateur") 
     *  @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id", nullable=true)
     */
    private $utilisateur;

    /** @ORM\Column(type="string", nullable=true) */
    private $organisation;

    /** @ORM\Column(type="string", nullable=true) */
    private $nom_contact;

    /** @ORM\Column(type="string", nullable=true) */
    private $email_contact;

    /** @ORM\Column(type="string", nullable=true) */
    private $telephone_contact;

    /** @ORM\Column(type="text", nullable=true) */
    private $adresse;

    // getters & setters
}
