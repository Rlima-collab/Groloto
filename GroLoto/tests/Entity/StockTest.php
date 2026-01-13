<?php

namespace App\Tests\Entity;

use App\Entity\Stock;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    public function testGetSetId(): void
    {
        $stock = new Stock();
        $this->assertNull($stock->getId());
    }

    public function testGetSetNom(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getNom());
        
        $nom = 'Chaises';
        $result = $stock->setNom($nom);
        $this->assertSame($stock, $result);
        $this->assertEquals($nom, $stock->getNom());
    }

    public function testGetSetCategorieValid(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getCategorie());
        
        $result = $stock->setCategorie('bar');
        $this->assertSame($stock, $result);
        $this->assertEquals('bar', $stock->getCategorie());
        
        $stock->setCategorie('resto');
        $this->assertEquals('resto', $stock->getCategorie());
        
        $stock->setCategorie('deco');
        $this->assertEquals('deco', $stock->getCategorie());
        
        $stock->setCategorie('autre');
        $this->assertEquals('autre', $stock->getCategorie());
    }

    public function testSetCategorieInvalidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $stock = new Stock();
        $stock->setCategorie('invalid');
    }

    public function testSetCategorieNull(): void
    {
        $stock = new Stock();
        $result = $stock->setCategorie(null);
        $this->assertSame($stock, $result);
        $this->assertNull($stock->getCategorie());
    }

    public function testGetSetQuantite(): void
    {
        $stock = new Stock();
        
        $this->assertEquals(0, $stock->getQuantite());
        
        $result = $stock->setQuantite(50);
        $this->assertSame($stock, $result);
        $this->assertEquals(50, $stock->getQuantite());
    }

    public function testGetSetUnite(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getUnite());
        
        $unite = 'pièces';
        $result = $stock->setUnite($unite);
        $this->assertSame($stock, $result);
        $this->assertEquals($unite, $stock->getUnite());
    }

    public function testGetSetSeuil(): void
    {
        $stock = new Stock();
        
        $this->assertEquals(0, $stock->getSeuil());
        
        $result = $stock->setSeuil(10);
        $this->assertSame($stock, $result);
        $this->assertEquals(10, $stock->getSeuil());
    }

    public function testGetSetValeurUnitaire(): void
    {
        $stock = new Stock();
        
        $this->assertEquals(0.0, $stock->getValeurUnitaire());
        
        $result = $stock->setValeurUnitaire(15.50);
        $this->assertSame($stock, $result);
        $this->assertEquals(15.50, $stock->getValeurUnitaire());
    }

    public function testGetSetRemarque(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getRemarque());
        
        $remarque = 'Test remarque';
        $result = $stock->setRemarque($remarque);
        $this->assertSame($stock, $result);
        $this->assertEquals($remarque, $stock->getRemarque());
    }

    public function testGetSetDerniereModif(): void
    {
        $stock = new Stock();
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $stock->setDerniereModif($date);
        $this->assertSame($stock, $result);
        $this->assertSame($date, $stock->getDerniereModif());
    }

    public function testGetSetSource(): void
    {
        $stock = new Stock();
        
        $this->assertEquals('achat', $stock->getSource());
        
        $result = $stock->setSource('pret');
        $this->assertSame($stock, $result);
        $this->assertEquals('pret', $stock->getSource());
    }

    public function testGetSetDateRetour(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getDateRetour());
        
        $date = new \DateTime('2025-02-01');
        $result = $stock->setDateRetour($date);
        $this->assertSame($stock, $result);
        $this->assertSame($date, $stock->getDateRetour());
    }

    public function testGetSetPreteur(): void
    {
        $stock = new Stock();
        
        $this->assertNull($stock->getPreteur());
        
        $preteur = 'Jean Dupont';
        $result = $stock->setPreteur($preteur);
        $this->assertSame($stock, $result);
        $this->assertEquals($preteur, $stock->getPreteur());
    }
}
