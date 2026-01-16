<?php

namespace App\Tests\Entity;

use App\Entity\HistoriqueEvenement;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class HistoriqueEvenementTest extends TestCase
{
    public function testGetSetId(): void
    {
        $historique = new HistoriqueEvenement();
        $this->assertNull($historique->getId());
    }

    public function testGetSetEvenement(): void
    {
        $historique = new HistoriqueEvenement();
        $evenement = new Evenement();
        
        $this->assertNull($historique->getEvenement());
        
        $result = $historique->setEvenement($evenement);
        $this->assertSame($historique, $result);
        $this->assertSame($evenement, $historique->getEvenement());
    }

    public function testGetSetActionValid(): void
    {
        $historique = new HistoriqueEvenement();
        
        $this->assertNull($historique->getAction());
        
        $result = $historique->setAction('creation');
        $this->assertSame($historique, $result);
        $this->assertEquals('creation', $historique->getAction());
        
        $historique->setAction('modification');
        $this->assertEquals('modification', $historique->getAction());
        
        $historique->setAction('annulation');
        $this->assertEquals('annulation', $historique->getAction());
        
        $historique->setAction('autre');
        $this->assertEquals('autre', $historique->getAction());
    }

    public function testSetActionInvalidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $historique = new HistoriqueEvenement();
        $historique->setAction('invalid');
    }

    public function testGetSetDescription(): void
    {
        $historique = new HistoriqueEvenement();
        
        $this->assertNull($historique->getDescription());
        
        $description = 'Test description';
        $result = $historique->setDescription($description);
        $this->assertSame($historique, $result);
        $this->assertEquals($description, $historique->getDescription());
    }

    public function testGetSetUtilisateur(): void
    {
        $historique = new HistoriqueEvenement();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($historique->getUtilisateur());
        
        $result = $historique->setUtilisateur($utilisateur);
        $this->assertSame($historique, $result);
        $this->assertSame($utilisateur, $historique->getUtilisateur());
    }

    public function testGetSetDateAction(): void
    {
        $historique = new HistoriqueEvenement();
        
        $this->assertNull($historique->getDateAction());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $historique->setDateAction($date);
        $this->assertSame($historique, $result);
        $this->assertSame($date, $historique->getDateAction());
    }
}
