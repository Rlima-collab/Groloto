<?php

namespace App\Tests\Entity;

use App\Entity\Creneau;
use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class CreneauTest extends TestCase
{
    public function testGetSetId(): void
    {
        $creneau = new Creneau();
        $this->assertNull($creneau->getId());
    }

    public function testGetSetEvenement(): void
    {
        $creneau = new Creneau();
        $evenement = new Evenement();
        
        $this->assertNull($creneau->getEvenement());
        
        $result = $creneau->setEvenement($evenement);
        $this->assertSame($creneau, $result);
        $this->assertSame($evenement, $creneau->getEvenement());
    }

    public function testGetSetTitre(): void
    {
        $creneau = new Creneau();
        
        $titre = 'Créneau Test';
        $result = $creneau->setTitre($titre);
        $this->assertSame($creneau, $result);
        $this->assertEquals($titre, $creneau->getTitre());
    }

    public function testGetSetPosteRequis(): void
    {
        $creneau = new Creneau();
        
        $this->assertNull($creneau->getPosteRequis());
        
        $poste = 'Animateur';
        $result = $creneau->setPosteRequis($poste);
        $this->assertSame($creneau, $result);
        $this->assertEquals($poste, $creneau->getPosteRequis());
    }

    public function testGetSetDebut(): void
    {
        $creneau = new Creneau();
        
        $debut = new \DateTime('2025-01-15 10:00:00');
        $result = $creneau->setDebut($debut);
        $this->assertSame($creneau, $result);
        $this->assertSame($debut, $creneau->getDebut());
    }

    public function testGetSetFin(): void
    {
        $creneau = new Creneau();
        
        $fin = new \DateTime('2025-01-15 18:00:00');
        $result = $creneau->setFin($fin);
        $this->assertSame($creneau, $result);
        $this->assertSame($fin, $creneau->getFin());
    }

    public function testGetSetMaxPersonnes(): void
    {
        $creneau = new Creneau();
        
        $this->assertEquals(1, $creneau->getMaxPersonnes());
        
        $result = $creneau->setMaxPersonnes(5);
        $this->assertSame($creneau, $result);
        $this->assertEquals(5, $creneau->getMaxPersonnes());
    }

    public function testGetSetRemarque(): void
    {
        $creneau = new Creneau();
        
        $this->assertNull($creneau->getRemarque());
        
        $remarque = 'Remarque test';
        $result = $creneau->setRemarque($remarque);
        $this->assertSame($creneau, $result);
        $this->assertEquals($remarque, $creneau->getRemarque());
    }

    public function testGetSetDateCreation(): void
    {
        $creneau = new Creneau();
        
        $this->assertNull($creneau->getDateCreation());
        
        $date = new \DateTime('2025-01-10 09:00:00');
        $result = $creneau->setDateCreation($date);
        $this->assertSame($creneau, $result);
        $this->assertSame($date, $creneau->getDateCreation());
    }
}
