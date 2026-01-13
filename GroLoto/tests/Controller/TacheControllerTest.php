<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class TacheControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\TacheController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\TacheController', 'index'));
    }

    public function testEditMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\TacheController', 'edit'));
    }
}
