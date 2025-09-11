<?php
// src/Entity/Creneau.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CRENEAU")
 */
class Creneau
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Evenement") 
     *  @ORM\JoinColumn(name="id_evenement", referencedColumnName="id", nullable=false)
     */
    private $evenement;

    /** @ORM\Column(type="string") */
    private $titre;

    /** @ORM\Column(type="string", nullable=true) */
    private $poste_requis;

    /** @ORM\Column(type="datetime") */
    private $debut;

    /** @ORM\Column(type="datetime") */
    private $fin;

    /** @ORM\Column(type="integer") */
    private $max_personnes = 1;

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
