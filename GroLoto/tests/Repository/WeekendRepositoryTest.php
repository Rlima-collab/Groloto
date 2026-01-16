<?php

namespace App\Tests\Repository;

use App\Repository\WeekendRepository;
use PHPUnit\Framework\TestCase;

class WeekendRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(WeekendRepository::class));
    }
}
