<?php

namespace App\Tests\Repository;

use App\Repository\AffectationTacheRepository;
use PHPUnit\Framework\TestCase;

class AffectationTacheRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(AffectationTacheRepository::class));
    }
}
