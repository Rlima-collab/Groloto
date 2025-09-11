<?php
// src/Entity/Convention.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CONVENTION")
 */
class Convention
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Mecene") 
     *  @ORM\JoinColumn(name="id_mecene", referencedColumnName="id", nullable=false)
     */
    private $mecene;

    /** @ORM\Column(type="string", nullable=true) */
    private $nom_modele;

    /** @ORM\Column(type="string", nullable=true) */
    private $url_pdf;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_signature;

    /** @ORM\Column(type="string", nullable=false, options={"default":"aucune"}) */
    private $methode_signature = 'aucune';

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
