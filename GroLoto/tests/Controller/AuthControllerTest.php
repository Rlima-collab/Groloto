<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\AuthController'));
    }

    public function testLoginMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\AuthController', 'login'));
    }

    public function testLogoutMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\AuthController', 'logout'));
    }
}
