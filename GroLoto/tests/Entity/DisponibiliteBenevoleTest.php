<?php

namespace App\Tests\Entity;

use App\Entity\DisponibiliteBenevole;
use App\Entity\Benevole;
use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class DisponibiliteBenevoleTest extends TestCase
{
    public function testGetSetId(): void
    {
        $dispo = new DisponibiliteBenevole();
        $this->assertNull($dispo->getId());
    }

    public function testGetSetBenevole(): void
    {
        $dispo = new DisponibiliteBenevole();
        $benevole = new Benevole();
        
        $this->assertNull($dispo->getBenevole());
        
        $result = $dispo->setBenevole($benevole);
        $this->assertSame($dispo, $result);
        $this->assertSame($benevole, $dispo->getBenevole());
    }

    public function testGetSetEvenement(): void
    {
        $dispo = new DisponibiliteBenevole();
        $evenement = new Evenement();
        
        $this->assertNull($dispo->getEvenement());
        
        $result = $dispo->setEvenement($evenement);
        $this->assertSame($dispo, $result);
        $this->assertSame($evenement, $dispo->getEvenement());
    }

    public function testGetSetDebut(): void
    {
        $dispo = new DisponibiliteBenevole();
        
        $this->assertNull($dispo->getDebut());
        
        $debut = new \DateTime('2025-01-15 10:00:00');
        $result = $dispo->setDebut($debut);
        $this->assertSame($dispo, $result);
        $this->assertSame($debut, $dispo->getDebut());
    }

    public function testGetSetFin(): void
    {
        $dispo = new DisponibiliteBenevole();
        
        $this->assertNull($dispo->getFin());
        
        $fin = new \DateTime('2025-01-15 18:00:00');
        $result = $dispo->setFin($fin);
        $this->assertSame($dispo, $result);
        $this->assertSame($fin, $dispo->getFin());
    }

    public function testGetSetNotes(): void
    {
        $dispo = new DisponibiliteBenevole();
        
        $this->assertNull($dispo->getNotes());
        
        $notes = 'Notes de disponibilité';
        $result = $dispo->setNotes($notes);
        $this->assertSame($dispo, $result);
        $this->assertEquals($notes, $dispo->getNotes());
    }

    public function testGetSetDateCreation(): void
    {
        $dispo = new DisponibiliteBenevole();
        
        $this->assertNull($dispo->getDateCreation());
        
        $date = new \DateTime('2025-01-10 09:00:00');
        $result = $dispo->setDateCreation($date);
        $this->assertSame($dispo, $result);
        $this->assertSame($date, $dispo->getDateCreation());
    }
}
