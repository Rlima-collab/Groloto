<?php

namespace App\Tests\Entity;

use App\Entity\Benevole;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class BenevoleTest extends TestCase
{
    public function testGetSetId(): void
    {
        $benevole = new Benevole();
        $this->assertNull($benevole->getId());
    }

    public function testGetSetUtilisateur(): void
    {
        $benevole = new Benevole();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($benevole->getUtilisateur());
        
        $result = $benevole->setUtilisateur($utilisateur);
        $this->assertSame($benevole, $result);
        $this->assertSame($utilisateur, $benevole->getUtilisateur());
    }

    public function testGetSetRemarque(): void
    {
        $benevole = new Benevole();
        
        $this->assertNull($benevole->getRemarque());
        
        $remarque = 'Test remarque';
        $result = $benevole->setRemarque($remarque);
        $this->assertSame($benevole, $result);
        $this->assertEquals($remarque, $benevole->getRemarque());
        
        $benevole->setRemarque(null);
        $this->assertNull($benevole->getRemarque());
    }

    public function testIsSetActif(): void
    {
        $benevole = new Benevole();
        
        $this->assertTrue($benevole->isActif());
        
        $result = $benevole->setActif(false);
        $this->assertSame($benevole, $result);
        $this->assertFalse($benevole->isActif());
        
        $benevole->setActif(true);
        $this->assertTrue($benevole->isActif());
    }

    public function testGetDisponibilitesEmpty(): void
    {
        $benevole = new Benevole();
        $this->assertEquals([], $benevole->getDisponibilites());
    }

    public function testSetGetDisponibilites(): void
    {
        $benevole = new Benevole();
        $dispos = ['lundi' => '09:00-17:00', 'mardi' => '10:00-18:00'];
        
        $result = $benevole->setDisponibilites($dispos);
        $this->assertSame($benevole, $result);
        
        $retrieved = $benevole->getDisponibilites();
        $this->assertIsArray($retrieved);
        $this->assertCount(2, $retrieved);
    }

    public function testSetDisponibilitesNull(): void
    {
        $benevole = new Benevole();
        $benevole->setDisponibilites(['test']);
        
        $result = $benevole->setDisponibilites(null);
        $this->assertSame($benevole, $result);
        $this->assertEquals([], $benevole->getDisponibilites());
    }

    public function testSetDisponibilitesEmptyArray(): void
    {
        $benevole = new Benevole();
        $result = $benevole->setDisponibilites([]);
        $this->assertSame($benevole, $result);
        $this->assertEquals([], $benevole->getDisponibilites());
    }
}
