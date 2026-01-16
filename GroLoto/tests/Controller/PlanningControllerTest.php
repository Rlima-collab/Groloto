<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class PlanningControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\PlanningController'));
    }
}
