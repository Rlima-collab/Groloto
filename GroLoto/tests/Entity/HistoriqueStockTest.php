<?php

namespace App\Tests\Entity;

use App\Entity\HistoriqueStock;
use App\Entity\Stock;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class HistoriqueStockTest extends TestCase
{
    public function testGetSetId(): void
    {
        $historique = new HistoriqueStock();
        $this->assertNull($historique->getId());
    }

    public function testGetSetStock(): void
    {
        $historique = new HistoriqueStock();
        $stock = new Stock();
        
        $this->assertNull($historique->getStock());
        
        $result = $historique->setStock($stock);
        $this->assertSame($historique, $result);
        $this->assertSame($stock, $historique->getStock());
    }

    public function testGetSetTypeChangement(): void
    {
        $historique = new HistoriqueStock();
        
        $type = 'ajout';
        $result = $historique->setTypeChangement($type);
        $this->assertSame($historique, $result);
        $this->assertEquals($type, $historique->getTypeChangement());
    }

    public function testGetSetQuantite(): void
    {
        $historique = new HistoriqueStock();
        
        $result = $historique->setQuantite(25);
        $this->assertSame($historique, $result);
        $this->assertEquals(25, $historique->getQuantite());
    }

    public function testGetSetRaison(): void
    {
        $historique = new HistoriqueStock();
        
        $this->assertNull($historique->getRaison());
        
        $raison = 'Réassort';
        $result = $historique->setRaison($raison);
        $this->assertSame($historique, $result);
        $this->assertEquals($raison, $historique->getRaison());
    }

    public function testGetSetEvenement(): void
    {
        $historique = new HistoriqueStock();
        $evenement = new Evenement();
        
        $this->assertNull($historique->getEvenement());
        
        $result = $historique->setEvenement($evenement);
        $this->assertSame($historique, $result);
        $this->assertSame($evenement, $historique->getEvenement());
    }

    public function testGetSetUtilisateur(): void
    {
        $historique = new HistoriqueStock();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($historique->getUtilisateur());
        
        $result = $historique->setUtilisateur($utilisateur);
        $this->assertSame($historique, $result);
        $this->assertSame($utilisateur, $historique->getUtilisateur());
    }

    public function testGetSetDateCreation(): void
    {
        $historique = new HistoriqueStock();
        
        $this->assertNull($historique->getDateCreation());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $historique->setDateCreation($date);
        $this->assertSame($historique, $result);
        $this->assertSame($date, $historique->getDateCreation());
    }
}
