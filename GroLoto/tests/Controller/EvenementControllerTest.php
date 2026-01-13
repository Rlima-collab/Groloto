<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class EvenementControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\EvenementController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\EvenementController', 'index'));
    }

    public function testCreateMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\EvenementController', 'create'));
    }

    public function testEditMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\EvenementController', 'edit'));
    }

    public function testDeleteMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\EvenementController', 'delete'));
    }
}
