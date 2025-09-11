<?php
// src/Entity/HelloAsso.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="HELLOASSO")
 */
class HelloAsso
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Evenement") 
     *  @ORM\JoinColumn(name="id_evenement", referencedColumnName="id", nullable=true)
     */
    private $evenement;

    /** @ORM\Column(type="string", nullable=true) */
    private $id_externe;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenom;

    /** @ORM\Column(type="string", nullable=true) */
    private $nom;

    /** @ORM\Column(type="string", nullable=true) */
    private $email;

    /** @ORM\Column(type="string", nullable=true) */
    private $telephone;

    /** @ORM\Column(type="string", nullable=true) */
    private $type_ticket;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_achat;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_import;

    // getters & setters
}
