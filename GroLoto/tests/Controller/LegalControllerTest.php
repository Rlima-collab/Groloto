<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class LegalControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\LegalController'));
    }
}
