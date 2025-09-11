<?php
// src/Entity/Stock.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="STOCK")
 */
class Stock
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string") */
    private $nom;

    /** @ORM\Column(type="string", nullable=true) */
    private $categorie;

    /** @ORM\Column(type="integer") */
    private $quantite = 0;

    /** @ORM\Column(type="string", nullable=true) */
    private $unite;

    /** @ORM\Column(type="integer") */
    private $seuil = 0;

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $derniere_modif;

    // getters & setters
}
