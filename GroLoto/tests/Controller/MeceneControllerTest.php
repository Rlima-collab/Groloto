<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class MeceneControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\MeceneController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\MeceneController', 'index'));
    }
}
