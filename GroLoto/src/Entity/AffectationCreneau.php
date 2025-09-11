<?php
// src/Entity/AffectationCreneau.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="AFFECTATION_CRENEAU")
 */
class AffectationCreneau
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Creneau") 
     *  @ORM\JoinColumn(name="id_creneau", referencedColumnName="id", nullable=false)
     */
    private $creneau;

    /** @ORM\ManyToOne(targetEntity="Benevole") 
     *  @ORM\JoinColumn(name="id_benevole", referencedColumnName="id", nullable=false)
     */
    private $benevole;

    /** @ORM\ManyToOne(targetEntity="Utilisateur") 
     *  @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id", nullable=true)
     */
    private $utilisateur;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_affectation;

    /** @ORM\Column(type="string", nullable=true, options={"default":"assigne"}) */
    private $statut = 'assigne';

    /** @ORM\Column(type="text", nullable=true) */
    private $notes;

    // getters & setters
}
