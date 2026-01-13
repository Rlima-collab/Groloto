<?php

namespace App\Tests\Repository;

use App\Repository\DemandeAnnulationRepository;
use PHPUnit\Framework\TestCase;

class DemandeAnnulationRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(DemandeAnnulationRepository::class));
    }
}
