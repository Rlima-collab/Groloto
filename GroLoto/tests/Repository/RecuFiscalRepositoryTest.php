<?php

namespace App\Tests\Repository;

use App\Repository\RecuFiscalRepository;
use PHPUnit\Framework\TestCase;

class RecuFiscalRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(RecuFiscalRepository::class));
    }
}
