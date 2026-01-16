<?php

namespace App\Tests\Repository;

use App\Repository\LotRepository;
use PHPUnit\Framework\TestCase;

class LotRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(LotRepository::class));
    }
}
