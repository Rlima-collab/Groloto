<?php
// src/Entity/Benevole.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="BENEVOLE")
 */
class Benevole
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\OneToOne(targetEntity="Utilisateur") 
     *  @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id", nullable=false)
     */
    private $utilisateur;

    /** @ORM\Column(type="text", nullable=true) */
    private $adresse;

    /** @ORM\Column(type="text", nullable=true) */
    private $contact_urgence;

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    /** @ORM\Column(type="boolean") */
    private $actif = true;

    // getters & setters
}
