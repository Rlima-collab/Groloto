<?php

namespace App\Tests\Entity;

use App\Entity\Tache;
use App\Entity\Weekend;
use App\Entity\Evenement;
use App\Entity\PlageHoraire;
use App\Entity\AffectationTache;
use PHPUnit\Framework\TestCase;

class TacheTest extends TestCase
{
    public function testGetSetId(): void
    {
        $tache = new Tache();
        $this->assertNull($tache->getId());
    }

    public function testGetSetTitre(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getTitre());
        
        $titre = 'Test Tache';
        $result = $tache->setTitre($titre);
        $this->assertSame($tache, $result);
        $this->assertEquals($titre, $tache->getTitre());
    }

    public function testGetSetDebut(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getDebut());
        
        $debut = new \DateTime('2025-01-15 10:00:00');
        $result = $tache->setDebut($debut);
        $this->assertSame($tache, $result);
        $this->assertSame($debut, $tache->getDebut());
    }

    public function testGetSetFin(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getFin());
        
        $fin = new \DateTime('2025-01-15 18:00:00');
        $result = $tache->setFin($fin);
        $this->assertSame($tache, $result);
        $this->assertSame($fin, $tache->getFin());
    }

    public function testGetSetMaxPersonnes(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getMaxPersonnes());
        
        $result = $tache->setMaxPersonnes(5);
        $this->assertSame($tache, $result);
        $this->assertEquals(5, $tache->getMaxPersonnes());
    }

    public function testGetSetRemarque(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getRemarque());
        
        $remarque = 'Test remarque';
        $result = $tache->setRemarque($remarque);
        $this->assertSame($tache, $result);
        $this->assertEquals($remarque, $tache->getRemarque());
    }

    public function testGetSetPosteRequis(): void
    {
        $tache = new Tache();
        
        $this->assertNull($tache->getPosteRequis());
        
        $poste = 'Responsable';
        $result = $tache->setPosteRequis($poste);
        $this->assertSame($tache, $result);
        $this->assertEquals($poste, $tache->getPosteRequis());
    }

    public function testGetSetWeekend(): void
    {
        $tache = new Tache();
        $weekend = new Weekend();
        
        $this->assertNull($tache->getWeekend());
        
        $result = $tache->setWeekend($weekend);
        $this->assertSame($tache, $result);
        $this->assertSame($weekend, $tache->getWeekend());
    }

    public function testGetSetEvenement(): void
    {
        $tache = new Tache();
        $evenement = new Evenement();
        
        $this->assertNull($tache->getEvenement());
        
        $result = $tache->setEvenement($evenement);
        $this->assertSame($tache, $result);
        $this->assertSame($evenement, $tache->getEvenement());
    }

    public function testPlagesHorairesInitiallyEmpty(): void
    {
        $tache = new Tache();
        $this->assertCount(0, $tache->getPlagesHoraires());
    }

    public function testAddPlageHoraire(): void
    {
        $tache = new Tache();
        $plage = new PlageHoraire();
        
        $result = $tache->addPlageHoraire($plage);
        $this->assertSame($tache, $result);
        $this->assertCount(1, $tache->getPlagesHoraires());
        $this->assertTrue($tache->getPlagesHoraires()->contains($plage));
        $this->assertSame($tache, $plage->getTache());
    }

    public function testRemovePlageHoraire(): void
    {
        $tache = new Tache();
        $plage = new PlageHoraire();
        
        $tache->addPlageHoraire($plage);
        $this->assertCount(1, $tache->getPlagesHoraires());
        
        $result = $tache->removePlageHoraire($plage);
        $this->assertSame($tache, $result);
        $this->assertCount(0, $tache->getPlagesHoraires());
    }

    public function testAffectationsInitiallyEmpty(): void
    {
        $tache = new Tache();
        $this->assertCount(0, $tache->getAffectations());
    }

    public function testAddAffectation(): void
    {
        $tache = new Tache();
        $affectation = new AffectationTache();
        
        $result = $tache->addAffectation($affectation);
        $this->assertSame($tache, $result);
        $this->assertCount(1, $tache->getAffectations());
        $this->assertTrue($tache->getAffectations()->contains($affectation));
    }

    public function testRemoveAffectation(): void
    {
        $tache = new Tache();
        $affectation = new AffectationTache();
        
        $tache->addAffectation($affectation);
        $this->assertCount(1, $tache->getAffectations());
        
        $result = $tache->removeAffectation($affectation);
        $this->assertSame($tache, $result);
        $this->assertCount(0, $tache->getAffectations());
    }
}
