<?php

namespace App\Tests\Repository;

use App\Repository\InscriptionMeceneRepository;
use PHPUnit\Framework\TestCase;

class InscriptionMeceneRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(InscriptionMeceneRepository::class));
    }
}
