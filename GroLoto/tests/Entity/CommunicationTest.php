<?php

namespace App\Tests\Entity;

use App\Entity\Communication;
use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class CommunicationTest extends TestCase
{
    public function testGetSetId(): void
    {
        $com = new Communication();
        $this->assertNull($com->getId());
    }

    public function testGetSetEvenement(): void
    {
        $com = new Communication();
        $evenement = new Evenement();
        
        $this->assertNull($com->getEvenement());
        
        $result = $com->setEvenement($evenement);
        $this->assertSame($com, $result);
        $this->assertSame($evenement, $com->getEvenement());
    }

    public function testGetSetTitre(): void
    {
        $com = new Communication();
        
        $titre = 'Communication Test';
        $result = $com->setTitre($titre);
        $this->assertSame($com, $result);
        $this->assertEquals($titre, $com->getTitre());
    }

    public function testGetSetType(): void
    {
        $com = new Communication();
        
        $this->assertEquals('post', $com->getType());
        
        $result = $com->setType('email');
        $this->assertSame($com, $result);
        $this->assertEquals('email', $com->getType());
    }

    public function testGetSetDatePrevue(): void
    {
        $com = new Communication();
        
        $this->assertNull($com->getDatePrevue());
        
        $date = new \DateTime('2025-02-01 10:00:00');
        $result = $com->setDatePrevue($date);
        $this->assertSame($com, $result);
        $this->assertSame($date, $com->getDatePrevue());
    }

    public function testGetSetStatut(): void
    {
        $com = new Communication();
        
        $this->assertEquals('brouillon', $com->getStatut());
        
        $result = $com->setStatut('publie');
        $this->assertSame($com, $result);
        $this->assertEquals('publie', $com->getStatut());
    }

    public function testGetSetBudget(): void
    {
        $com = new Communication();
        
        $this->assertEquals(0.0, $com->getBudget());
        
        $result = $com->setBudget(150.75);
        $this->assertSame($com, $result);
        $this->assertEquals(150.75, $com->getBudget());
    }

    public function testGetSetNotes(): void
    {
        $com = new Communication();
        
        $this->assertNull($com->getNotes());
        
        $notes = 'Notes importantes';
        $result = $com->setNotes($notes);
        $this->assertSame($com, $result);
        $this->assertEquals($notes, $com->getNotes());
    }

    public function testGetSetDateCreation(): void
    {
        $com = new Communication();
        
        $this->assertNull($com->getDateCreation());
        
        $date = new \DateTime('2025-01-10 09:00:00');
        $result = $com->setDateCreation($date);
        $this->assertSame($com, $result);
        $this->assertSame($date, $com->getDateCreation());
    }
}
