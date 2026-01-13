<?php

namespace App\Tests\Repository;

use App\Repository\BenevoleRepository;
use PHPUnit\Framework\TestCase;

class BenevoleRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(BenevoleRepository::class));
    }
}
