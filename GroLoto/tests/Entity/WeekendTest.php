<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Weekend;
use App\Entity\Tache;

class WeekendTest extends TestCase
{
    public function testDateSamediAndAllDays()
    {
        $weekend = new Weekend();
        $debut = new \DateTime('2025-01-10'); // vendredi
        $fin = new \DateTime('2025-01-12'); // dimanche
        $weekend->setDateDebut($debut)->setDateFin($fin);

        $samedi = $weekend->getDateSamedi();
        $this->assertInstanceOf(\DateTimeInterface::class, $samedi);
        $this->assertEquals('2025-01-11', $samedi->format('Y-m-d'));

        $days = $weekend->getAllDays();
        $this->assertCount(3, $days);
        $this->assertEquals('2025-01-10', $days[0]->format('Y-m-d'));
        $this->assertEquals('2025-01-11', $days[1]->format('Y-m-d'));
        $this->assertEquals('2025-01-12', $days[2]->format('Y-m-d'));
    }

    public function testAddAndRemoveTache()
    {
        $weekend = new Weekend();
        $tache = new Tache();
        $tache->setTitre('Accueil');

        $weekend->addTache($tache);
        $this->assertCount(1, $weekend->getTaches());
        $this->assertSame($weekend, $tache->getWeekend());

        $weekend->removeTache($tache);
        $this->assertCount(0, $weekend->getTaches());
        $this->assertNull($tache->getWeekend());
    }
}
