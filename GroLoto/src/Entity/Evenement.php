<?php
// src/Entity/Evenement.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="EVENEMENT")
 */
class Evenement
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string") */
    private $nom;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="date", nullable=true) */
    private $date_debut;

    /** @ORM\Column(type="date", nullable=true) */
    private $date_fin;

    /** @ORM\Column(type="string", nullable=true) */
    private $lieu;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
