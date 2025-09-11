<?php
// src/Entity/HistoriqueStock.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="HISTORIQUE_STOCK")
 */
class HistoriqueStock
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Stock") 
     *  @ORM\JoinColumn(name="id_stock", referencedColumnName="id", nullable=false)
     */
    private $stock;

    /** @ORM\Column(type="string") */
    private $type_changement;

    /** @ORM\Column(type="integer") */
    private $quantite;

    /** @ORM\Column(type="text", nullable=true) */
    private $raison;

    /** @ORM\ManyToOne(targetEntity="Evenement") 
     *  @ORM\JoinColumn(name="id_evenement", referencedColumnName="id", nullable=true)
     */
    private $evenement;

    /** @ORM\ManyToOne(targetEntity="Utilisateur") 
     *  @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id", nullable=true)
     */
    private $utilisateur;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $date_creation;

    // getters & setters
}
