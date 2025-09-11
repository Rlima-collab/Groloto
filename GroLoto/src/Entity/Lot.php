<?php
// src/Entity/Lot.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="LOT")
 */
class Lot
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Mecene") 
     *  @ORM\JoinColumn(name="id_mecene", referencedColumnName="id", nullable=true)
     */
    private $mecene;

    /** @ORM\Column(type="string") */
    private $titre;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="integer") */
    private $quantite = 1;

    /** @ORM\Column(type="float") */
    private $valeur_estimee = 0.0;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
