<?php

namespace App\Tests\Repository;

use App\Repository\EvenementRepository;
use PHPUnit\Framework\TestCase;

class EvenementRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(EvenementRepository::class));
    }
}
