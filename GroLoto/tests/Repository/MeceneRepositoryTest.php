<?php

namespace App\Tests\Repository;

use App\Repository\MeceneRepository;
use PHPUnit\Framework\TestCase;

class MeceneRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(MeceneRepository::class));
    }
}
