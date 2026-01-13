<?php

namespace App\Tests\Entity;

use App\Entity\AffectationTache;
use App\Entity\Tache;
use App\Entity\Benevole;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class AffectationTacheTest extends TestCase
{
    public function testGetSetId(): void
    {
        $affectation = new AffectationTache();
        $this->assertNull($affectation->getId());
    }

    public function testGetSetTache(): void
    {
        $affectation = new AffectationTache();
        $tache = new Tache();
        
        $this->assertNull($affectation->getTache());
        
        $result = $affectation->setTache($tache);
        $this->assertSame($affectation, $result);
        $this->assertSame($tache, $affectation->getTache());
    }

    public function testGetSetBenevole(): void
    {
        $affectation = new AffectationTache();
        $benevole = new Benevole();
        
        $this->assertNull($affectation->getBenevole());
        
        $result = $affectation->setBenevole($benevole);
        $this->assertSame($affectation, $result);
        $this->assertSame($benevole, $affectation->getBenevole());
    }

    public function testGetSetUtilisateur(): void
    {
        $affectation = new AffectationTache();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($affectation->getUtilisateur());
        
        $result = $affectation->setUtilisateur($utilisateur);
        $this->assertSame($affectation, $result);
        $this->assertSame($utilisateur, $affectation->getUtilisateur());
    }

    public function testGetSetDateAffectation(): void
    {
        $affectation = new AffectationTache();
        
        $this->assertNull($affectation->getDateAffectation());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $affectation->setDateAffectation($date);
        $this->assertSame($affectation, $result);
        $this->assertSame($date, $affectation->getDateAffectation());
    }

    public function testGetSetStatut(): void
    {
        $affectation = new AffectationTache();
        
        $this->assertNull($affectation->getStatut());
        
        $statut = 'confirme';
        $result = $affectation->setStatut($statut);
        $this->assertSame($affectation, $result);
        $this->assertEquals($statut, $affectation->getStatut());
    }

    public function testGetSetRemarque(): void
    {
        $affectation = new AffectationTache();
        
        $this->assertNull($affectation->getRemarque());
        
        $remarque = 'Test remarque';
        $result = $affectation->setRemarque($remarque);
        $this->assertSame($affectation, $result);
        $this->assertEquals($remarque, $affectation->getRemarque());
    }
}
