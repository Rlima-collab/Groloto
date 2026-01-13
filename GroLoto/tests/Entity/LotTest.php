<?php

namespace App\Tests\Entity;

use App\Entity\Lot;
use App\Entity\Mecene;
use App\Entity\Weekend;
use PHPUnit\Framework\TestCase;

class LotTest extends TestCase
{
    public function testGetSetId(): void
    {
        $lot = new Lot();
        $this->assertNull($lot->getId());
    }

    public function testGetSetMecene(): void
    {
        $lot = new Lot();
        $mecene = new Mecene();
        
        $this->assertNull($lot->getMecene());
        
        $result = $lot->setMecene($mecene);
        $this->assertSame($lot, $result);
        $this->assertSame($mecene, $lot->getMecene());
    }

    public function testGetSetWeekend(): void
    {
        $lot = new Lot();
        $weekend = new Weekend();
        
        $this->assertNull($lot->getWeekend());
        
        $result = $lot->setWeekend($weekend);
        $this->assertSame($lot, $result);
        $this->assertSame($weekend, $lot->getWeekend());
    }

    public function testGetSetTitre(): void
    {
        $lot = new Lot();
        
        $titre = 'Lot cadeau';
        $result = $lot->setTitre($titre);
        $this->assertSame($lot, $result);
        $this->assertEquals($titre, $lot->getTitre());
    }

    public function testGetSetDescription(): void
    {
        $lot = new Lot();
        
        $this->assertNull($lot->getDescription());
        
        $description = 'Une description';
        $result = $lot->setDescription($description);
        $this->assertSame($lot, $result);
        $this->assertEquals($description, $lot->getDescription());
    }

    public function testGetSetQuantite(): void
    {
        $lot = new Lot();
        
        $this->assertEquals(1, $lot->getQuantite());
        
        $result = $lot->setQuantite(5);
        $this->assertSame($lot, $result);
        $this->assertEquals(5, $lot->getQuantite());
    }

    public function testGetSetValeurEstimee(): void
    {
        $lot = new Lot();
        
        $this->assertEquals(0.0, $lot->getValeurEstimee());
        
        $result = $lot->setValeurEstimee(150.75);
        $this->assertSame($lot, $result);
        $this->assertEquals(150.75, $lot->getValeurEstimee());
    }

    public function testGetSetDateCreation(): void
    {
        $lot = new Lot();
        
        $this->assertNull($lot->getDateCreation());
        
        $date = new \DateTime('2025-01-15 10:00:00');
        $result = $lot->setDateCreation($date);
        $this->assertSame($lot, $result);
        $this->assertSame($date, $lot->getDateCreation());
    }
}
