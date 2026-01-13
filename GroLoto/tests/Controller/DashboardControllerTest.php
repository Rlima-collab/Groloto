<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\DashboardController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\DashboardController', 'index'));
    }
}
