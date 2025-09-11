<?php
// src/Entity/Communication.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="COMMUNICATION")
 */
class Communication
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Evenement") 
     *  @ORM\JoinColumn(name="id_evenement", referencedColumnName="id", nullable=true)
     */
    private $evenement;

    /** @ORM\Column(type="string") */
    private $titre;

    /** @ORM\Column(type="string", nullable=true) */
    private $type = 'post';

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_prevue;

    /** @ORM\Column(type="string", nullable=true) */
    private $statut = 'brouillon';

    /** @ORM\Column(type="float", nullable=true) */
    private $budget = 0.0;

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
