<?php

namespace App\Tests\Repository;

use App\Repository\RoleRepository;
use PHPUnit\Framework\TestCase;

class RoleRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(RoleRepository::class));
    }
}
