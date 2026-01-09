<?php

use PHPUnit\Framework\TestCase;
use App\Entity\DisponibiliteWeekend;

class DisponibiliteWeekendTest extends TestCase
{
    public function testHasAnyDisponibiliteAndResume()
    {
        $d = new DisponibiliteWeekend();
        $this->assertFalse($d->hasAnyDisponibilite());
        $this->assertEquals('Non disponible', $d->getCreneauxResume());

        $d->setMatin(true);
        $this->assertTrue($d->hasAnyDisponibilite());
        $this->assertEquals('Matin', $d->getCreneauxResume());

        $d->setApresMidi(true);
        $this->assertEquals('Matin, Après-midi', $d->getCreneauxResume());

        $d->setSoir(true);
        $this->assertEquals('Matin, Après-midi, Soir', $d->getCreneauxResume());
    }
}
