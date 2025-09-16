<?php
// src/Entity/DisponibiliteBenevole.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="DISPONIBILITE_BENEVOLE")
 */
class DisponibiliteBenevole
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Benevole") 
     *  @ORM\JoinColumn(name="id_benevole", referencedColumnName="id", nullable=false)
     */
    private $benevole;

    /** @ORM\ManyToOne(targetEntity="Evenement") 
     *  @ORM\JoinColumn(name="id_evenement", referencedColumnName="id", nullable=false)
     */
    private $evenement;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $debut;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $fin;

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
