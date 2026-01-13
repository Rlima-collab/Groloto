<?php

namespace App\Tests\Repository;

use App\Repository\PlageHoraireRepository;
use PHPUnit\Framework\TestCase;

class PlageHoraireRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(PlageHoraireRepository::class));
    }
}
