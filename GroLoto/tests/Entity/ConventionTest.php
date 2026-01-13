<?php

namespace App\Tests\Entity;

use App\Entity\Convention;
use App\Entity\Mecene;
use PHPUnit\Framework\TestCase;

class ConventionTest extends TestCase
{
    public function testGetSetId(): void
    {
        $convention = new Convention();
        $this->assertNull($convention->getId());
    }

    public function testGetSetMecene(): void
    {
        $convention = new Convention();
        $mecene = new Mecene();
        
        $this->assertNull($convention->getMecene());
        
        $result = $convention->setMecene($mecene);
        $this->assertSame($convention, $result);
        $this->assertSame($mecene, $convention->getMecene());
    }

    public function testGetSetNomModele(): void
    {
        $convention = new Convention();
        
        $this->assertNull($convention->getNomModele());
        
        $nom = 'Convention Standard';
        $result = $convention->setNomModele($nom);
        $this->assertSame($convention, $result);
        $this->assertEquals($nom, $convention->getNomModele());
    }

    public function testGetSetUrlPdf(): void
    {
        $convention = new Convention();
        
        $this->assertNull($convention->getUrlPdf());
        
        $url = '/uploads/convention_2025.pdf';
        $result = $convention->setUrlPdf($url);
        $this->assertSame($convention, $result);
        $this->assertEquals($url, $convention->getUrlPdf());
    }

    public function testGetSetDateSignature(): void
    {
        $convention = new Convention();
        
        $this->assertNull($convention->getDateSignature());
        
        $date = new \DateTime('2025-01-15 14:00:00');
        $result = $convention->setDateSignature($date);
        $this->assertSame($convention, $result);
        $this->assertSame($date, $convention->getDateSignature());
    }

    public function testGetSetMethodeSignature(): void
    {
        $convention = new Convention();
        
        $this->assertEquals('aucune', $convention->getMethodeSignature());
        
        $result = $convention->setMethodeSignature('electronique');
        $this->assertSame($convention, $result);
        $this->assertEquals('electronique', $convention->getMethodeSignature());
    }

    public function testGetSetDateCreation(): void
    {
        $convention = new Convention();
        
        $this->assertNull($convention->getDateCreation());
        
        $date = new \DateTime('2025-01-10 09:00:00');
        $result = $convention->setDateCreation($date);
        $this->assertSame($convention, $result);
        $this->assertSame($date, $convention->getDateCreation());
    }
}
