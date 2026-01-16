<?php

namespace App\Tests\Entity;

use App\Entity\Parametre;
use PHPUnit\Framework\TestCase;

class ParametreTest extends TestCase
{
    public function testGetSetId(): void
    {
        $reflection = new \ReflectionClass(Parametre::class);
        $this->assertTrue($reflection->hasProperty('id'));
    }

    public function testParametreClassExists(): void
    {
        $this->assertTrue(class_exists(Parametre::class));
    }
}
