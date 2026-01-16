<?php

namespace App\Tests\Entity;

use App\Entity\InscriptionMecene;
use App\Entity\Mecene;
use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class InscriptionMeceneTest extends TestCase
{
    public function testGetSetId(): void
    {
        $inscription = new InscriptionMecene();
        $this->assertNull($inscription->getId());
    }

    public function testGetSetMecene(): void
    {
        $inscription = new InscriptionMecene();
        $mecene = new Mecene();
        
        $this->assertNull($inscription->getMecene());
        
        $result = $inscription->setMecene($mecene);
        $this->assertSame($inscription, $result);
        $this->assertSame($mecene, $inscription->getMecene());
    }

    public function testGetSetEvenement(): void
    {
        $inscription = new InscriptionMecene();
        $evenement = new Evenement();
        
        $this->assertNull($inscription->getEvenement());
        
        $result = $inscription->setEvenement($evenement);
        $this->assertSame($inscription, $result);
        $this->assertSame($evenement, $inscription->getEvenement());
    }

    public function testGetSetDescriptionDon(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getDescriptionDon());
        
        $description = 'Don de matériel';
        $result = $inscription->setDescriptionDon($description);
        $this->assertSame($inscription, $result);
        $this->assertEquals($description, $inscription->getDescriptionDon());
    }

    public function testGetSetMontantEstime(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getMontantEstime());
        
        $result = $inscription->setMontantEstime(500.50);
        $this->assertSame($inscription, $result);
        $this->assertEquals(500.50, $inscription->getMontantEstime());
    }

    public function testGetSetStatut(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertEquals('en_attente', $inscription->getStatut());
        
        $result = $inscription->setStatut('accepte');
        $this->assertSame($inscription, $result);
        $this->assertEquals('accepte', $inscription->getStatut());
    }

    public function testGetSetDateInscription(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getDateInscription());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $inscription->setDateInscription($date);
        $this->assertSame($inscription, $result);
        $this->assertSame($date, $inscription->getDateInscription());
    }

    public function testGetSetRemarques(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getRemarques());
        
        $remarques = 'Remarques importantes';
        $result = $inscription->setRemarques($remarques);
        $this->assertSame($inscription, $result);
        $this->assertEquals($remarques, $inscription->getRemarques());
    }

    public function testGetSetNomDon(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getNomDon());
        
        $nom = 'Lot de chaises';
        $result = $inscription->setNomDon($nom);
        $this->assertSame($inscription, $result);
        $this->assertEquals($nom, $inscription->getNomDon());
    }

    public function testGetSetCategorie(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getCategorie());
        
        $categorie = 'materiel';
        $result = $inscription->setCategorie($categorie);
        $this->assertSame($inscription, $result);
        $this->assertEquals($categorie, $inscription->getCategorie());
    }

    public function testGetSetQuantite(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getQuantite());
        
        $result = $inscription->setQuantite(10);
        $this->assertSame($inscription, $result);
        $this->assertEquals(10, $inscription->getQuantite());
    }

    public function testGetSetValeurUnitaire(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getValeurUnitaire());
        
        $result = $inscription->setValeurUnitaire(25.99);
        $this->assertSame($inscription, $result);
        $this->assertEquals(25.99, $inscription->getValeurUnitaire());
    }

    public function testGetSetRemarqueRefus(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getRemarqueRefus());
        
        $remarque = 'Budget insuffisant';
        $result = $inscription->setRemarqueRefus($remarque);
        $this->assertSame($inscription, $result);
        $this->assertEquals($remarque, $inscription->getRemarqueRefus());
    }

    public function testGetSetRemarqueAcceptation(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getRemarqueAcceptation());
        
        $remarque = 'Merci pour votre don';
        $result = $inscription->setRemarqueAcceptation($remarque);
        $this->assertSame($inscription, $result);
        $this->assertEquals($remarque, $inscription->getRemarqueAcceptation());
    }

    public function testGetSetAdressePostale(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertNull($inscription->getAdressePostale());
        
        $adresse = '123 rue Test';
        $result = $inscription->setAdressePostale($adresse);
        $this->assertSame($inscription, $result);
        $this->assertEquals($adresse, $inscription->getAdressePostale());
    }

    public function testGetSetTypeDon(): void
    {
        $inscription = new InscriptionMecene();
        
        $this->assertEquals('fonctionnement', $inscription->getTypeDon());
        
        $result = $inscription->setTypeDon('materiel');
        $this->assertSame($inscription, $result);
        $this->assertEquals('materiel', $inscription->getTypeDon());
    }
}
