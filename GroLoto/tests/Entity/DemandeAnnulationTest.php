<?php

namespace App\Tests\Entity;

use App\Entity\DemandeAnnulation;
use App\Entity\AffectationTache;
use App\Entity\Benevole;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class DemandeAnnulationTest extends TestCase
{
    public function testGetSetId(): void
    {
        $demande = new DemandeAnnulation();
        $this->assertNull($demande->getId());
    }

    public function testGetSetAffectation(): void
    {
        $demande = new DemandeAnnulation();
        $affectation = new AffectationTache();
        
        $this->assertNull($demande->getAffectation());
        
        $result = $demande->setAffectation($affectation);
        $this->assertSame($demande, $result);
        $this->assertSame($affectation, $demande->getAffectation());
    }

    public function testGetSetBenevole(): void
    {
        $demande = new DemandeAnnulation();
        $benevole = new Benevole();
        
        $this->assertNull($demande->getBenevole());
        
        $result = $demande->setBenevole($benevole);
        $this->assertSame($demande, $result);
        $this->assertSame($benevole, $demande->getBenevole());
    }

    public function testGetSetDateDemande(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getDateDemande());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $demande->setDateDemande($date);
        $this->assertSame($demande, $result);
        $this->assertSame($date, $demande->getDateDemande());
    }

    public function testGetSetStatut(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getStatut());
        
        $result = $demande->setStatut('en_attente');
        $this->assertSame($demande, $result);
        $this->assertEquals('en_attente', $demande->getStatut());
    }

    public function testGetSetMotifBenevole(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getMotifBenevole());
        
        $motif = 'Problème de santé';
        $result = $demande->setMotifBenevole($motif);
        $this->assertSame($demande, $result);
        $this->assertEquals($motif, $demande->getMotifBenevole());
    }

    public function testGetSetTacheTitre(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getTacheTitre());
        
        $titre = 'Tâche Test';
        $result = $demande->setTacheTitre($titre);
        $this->assertSame($demande, $result);
        $this->assertEquals($titre, $demande->getTacheTitre());
    }

    public function testGetSetMessageAdmin(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getMessageAdmin());
        
        $message = 'Demande acceptée';
        $result = $demande->setMessageAdmin($message);
        $this->assertSame($demande, $result);
        $this->assertEquals($message, $demande->getMessageAdmin());
    }

    public function testGetSetDateReponse(): void
    {
        $demande = new DemandeAnnulation();
        
        $this->assertNull($demande->getDateReponse());
        
        $date = new \DateTime('2025-01-16 14:00:00');
        $result = $demande->setDateReponse($date);
        $this->assertSame($demande, $result);
        $this->assertSame($date, $demande->getDateReponse());
    }

    public function testGetSetAdminReponse(): void
    {
        $demande = new DemandeAnnulation();
        $admin = new Utilisateur();
        
        $this->assertNull($demande->getAdminReponse());
        
        $result = $demande->setAdminReponse($admin);
        $this->assertSame($demande, $result);
        $this->assertSame($admin, $demande->getAdminReponse());
    }
}
