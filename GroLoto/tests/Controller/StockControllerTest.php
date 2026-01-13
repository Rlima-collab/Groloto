<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class StockControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\StockController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\StockController', 'index'));
    }
}
