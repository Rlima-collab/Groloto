<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class WeekendControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\WeekendController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\WeekendController', 'index'));
    }

    public function testCreateMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\WeekendController', 'create'));
    }

    public function testEditMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\WeekendController', 'edit'));
    }
}
