<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class BenevoleControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\BenevoleController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\BenevoleController', 'index'));
    }

    public function testEditMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\BenevoleController', 'edit'));
    }
}
