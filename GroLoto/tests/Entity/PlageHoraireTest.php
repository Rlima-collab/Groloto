<?php

use PHPUnit\Framework\TestCase;
use App\Entity\PlageHoraire;

class PlageHoraireTest extends TestCase
{
    public function testGetDebutAndFin()
    {
        $pl = new PlageHoraire();
        $jour = new \DateTime('2025-02-01');
        $debutHeure = new \DateTime('1970-01-01 09:00:00');
        $finHeure = new \DateTime('1970-01-01 12:30:00');

        $pl->setJour($jour)->setHeureDebut($debutHeure)->setHeureFin($finHeure);

        $this->assertEquals('2025-02-01 09:00:00', $pl->getDebut()->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-02-01 12:30:00', $pl->getFin()->format('Y-m-d H:i:s'));
    }

    public function testGetDebutThrowsWhenMissing()
    {
        $this->expectException(RuntimeException::class);
        $pl = new PlageHoraire();
        $pl->getDebut();
    }
}
