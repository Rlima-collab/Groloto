<?php

namespace App\Tests\Entity;

use App\Entity\RecuFiscal;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class RecuFiscalTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $recu = new RecuFiscal();
        $this->assertInstanceOf(\DateTimeInterface::class, $recu->getCreatedAt());
    }

    public function testGetSetId(): void
    {
        $recu = new RecuFiscal();
        $this->assertNull($recu->getId());
    }

    public function testGetSetDestinataire(): void
    {
        $recu = new RecuFiscal();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($recu->getDestinataire());
        
        $result = $recu->setDestinataire($utilisateur);
        $this->assertSame($recu, $result);
        $this->assertSame($utilisateur, $recu->getDestinataire());
    }

    public function testGetSetType(): void
    {
        $recu = new RecuFiscal();
        
        $this->assertNull($recu->getType());
        
        $result = $recu->setType('mecene');
        $this->assertSame($recu, $result);
        $this->assertEquals('mecene', $recu->getType());
    }

    public function testGetSetFichier(): void
    {
        $recu = new RecuFiscal();
        
        $this->assertNull($recu->getFichier());
        
        $fichier = '/uploads/recu_2025.pdf';
        $result = $recu->setFichier($fichier);
        $this->assertSame($recu, $result);
        $this->assertEquals($fichier, $recu->getFichier());
    }

    public function testGetSetAnnee(): void
    {
        $recu = new RecuFiscal();
        
        $this->assertNull($recu->getAnnee());
        
        $result = $recu->setAnnee(2025);
        $this->assertSame($recu, $result);
        $this->assertEquals(2025, $recu->getAnnee());
    }

    public function testGetSetCreatedAt(): void
    {
        $recu = new RecuFiscal();
        $date = new \DateTime('2025-01-15 10:00:00');
        
        $result = $recu->setCreatedAt($date);
        $this->assertSame($recu, $result);
        $this->assertSame($date, $recu->getCreatedAt());
    }

    public function testGetSetEnvoyePar(): void
    {
        $recu = new RecuFiscal();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($recu->getEnvoyePar());
        
        $result = $recu->setEnvoyePar($utilisateur);
        $this->assertSame($recu, $result);
        $this->assertSame($utilisateur, $recu->getEnvoyePar());
    }
}
