<?php
// src/Entity/Parametre.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="PARAMETRE")
 */
class Parametre
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", unique=true) */
    private $cle;

    /** @ORM\Column(type="string", nullable=true) */
    private $valeur;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_modification;

    // getters & setters
}
