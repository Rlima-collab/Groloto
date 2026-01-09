<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Evenement;

class EvenementTest extends TestCase
{
    public function testHeureFinCalculation()
    {
        $evt = new Evenement();
        $heureDebut = new \DateTime('2025-01-10 10:00:00');
        $evt->setHeureDebut($heureDebut);
        $evt->setDureeMinutes(90);

        $heureFin = $evt->getHeureFin();
        $this->assertInstanceOf(\DateTimeInterface::class, $heureFin);
        $this->assertEquals('2025-01-10 11:30:00', $heureFin->format('Y-m-d H:i:s'));
    }

    public function testDefaultValues()
    {
        $evt = new Evenement();
        $this->assertNotNull($evt->getDateCreation());
    }
}
