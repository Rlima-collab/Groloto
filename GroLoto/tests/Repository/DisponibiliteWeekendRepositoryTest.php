<?php

namespace App\Tests\Repository;

use App\Repository\DisponibiliteWeekendRepository;
use PHPUnit\Framework\TestCase;

class DisponibiliteWeekendRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(DisponibiliteWeekendRepository::class));
    }
}
