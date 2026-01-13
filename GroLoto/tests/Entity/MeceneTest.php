<?php

namespace App\Tests\Entity;

use App\Entity\Mecene;
use App\Entity\Utilisateur;
use App\Entity\Lot;
use PHPUnit\Framework\TestCase;

class MeceneTest extends TestCase
{
    public function testGetSetId(): void
    {
        $mecene = new Mecene();
        $this->assertNull($mecene->getId());
    }

    public function testGetSetUtilisateur(): void
    {
        $mecene = new Mecene();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($mecene->getUtilisateur());
        
        $result = $mecene->setUtilisateur($utilisateur);
        $this->assertSame($mecene, $result);
        $this->assertSame($utilisateur, $mecene->getUtilisateur());
    }

    public function testGetSetOrganisation(): void
    {
        $mecene = new Mecene();
        
        $organisation = 'Test Organisation';
        $result = $mecene->setOrganisation($organisation);
        $this->assertSame($mecene, $result);
        $this->assertEquals($organisation, $mecene->getOrganisation());
    }

    public function testGetSetSiret(): void
    {
        $mecene = new Mecene();
        
        $siret = '12345678901234';
        $result = $mecene->setSiret($siret);
        $this->assertSame($mecene, $result);
        $this->assertEquals($siret, $mecene->getSiret());
    }

    public function testGetSetAdressePostale(): void
    {
        $mecene = new Mecene();
        
        $this->assertNull($mecene->getAdressePostale());
        
        $adresse = '123 rue Test, 75000 Paris';
        $result = $mecene->setAdressePostale($adresse);
        $this->assertSame($mecene, $result);
        $this->assertEquals($adresse, $mecene->getAdressePostale());
    }

    public function testGetSetLogo(): void
    {
        $mecene = new Mecene();
        
        $this->assertNull($mecene->getLogo());
        
        $logo = 'logo.png';
        $result = $mecene->setLogo($logo);
        $this->assertSame($mecene, $result);
        $this->assertEquals($logo, $mecene->getLogo());
    }

    public function testGetSetInstagram(): void
    {
        $mecene = new Mecene();
        
        $this->assertNull($mecene->getInstagram());
        
        $instagram = '@testcompany';
        $result = $mecene->setInstagram($instagram);
        $this->assertSame($mecene, $result);
        $this->assertEquals($instagram, $mecene->getInstagram());
    }

    public function testGetSetFacebook(): void
    {
        $mecene = new Mecene();
        
        $this->assertNull($mecene->getFacebook());
        
        $facebook = 'fb.com/testcompany';
        $result = $mecene->setFacebook($facebook);
        $this->assertSame($mecene, $result);
        $this->assertEquals($facebook, $mecene->getFacebook());
    }

    public function testGetLotsInitiallyEmpty(): void
    {
        $mecene = new Mecene();
        $this->assertCount(0, $mecene->getLots());
    }

    public function testAddLot(): void
    {
        $mecene = new Mecene();
        $lot = new Lot();
        
        $result = $mecene->addLot($lot);
        $this->assertSame($mecene, $result);
        $this->assertCount(1, $mecene->getLots());
        $this->assertTrue($mecene->getLots()->contains($lot));
    }

    public function testAddLotDoesNotDuplicateExisting(): void
    {
        $mecene = new Mecene();
        $lot = new Lot();
        
        $mecene->addLot($lot);
        $mecene->addLot($lot);
        
        $this->assertCount(1, $mecene->getLots());
    }

    public function testRemoveLot(): void
    {
        $mecene = new Mecene();
        $lot = new Lot();
        
        $mecene->addLot($lot);
        $this->assertCount(1, $mecene->getLots());
        
        $result = $mecene->removeLot($lot);
        $this->assertSame($mecene, $result);
        $this->assertCount(0, $mecene->getLots());
        $this->assertFalse($mecene->getLots()->contains($lot));
    }
}
