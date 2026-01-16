<?php

namespace App\Tests\Entity;

use App\Entity\HelloAsso;
use PHPUnit\Framework\TestCase;

class HelloAssoTest extends TestCase
{
    public function testHelloAssoClassExists(): void
    {
        $this->assertTrue(class_exists(HelloAsso::class));
    }

    public function testHelloAssoHasIdProperty(): void
    {
        $reflection = new \ReflectionClass(HelloAsso::class);
        $this->assertTrue($reflection->hasProperty('id'));
    }
}
