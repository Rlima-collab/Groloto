<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class NotificationControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\NotificationController'));
    }

    public function testIndexMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\NotificationController', 'index'));
    }

    public function testMarkAsReadMethodExists(): void
    {
        $this->assertTrue(method_exists('App\Controller\NotificationController', 'markAsRead'));
    }
}
