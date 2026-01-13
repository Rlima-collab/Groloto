<?php

namespace App\Tests\Repository;

use App\Repository\TacheRepository;
use PHPUnit\Framework\TestCase;

class TacheRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(TacheRepository::class));
    }
}
