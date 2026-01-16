<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\HomeController'));
    }

    public function testIndexMethodExists(): void
    {
        $controller = new \App\Controller\HomeController();
        $this->assertTrue(method_exists($controller, 'index'));
    }
}
