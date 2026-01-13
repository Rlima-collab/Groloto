<?php

namespace App\Tests\Repository;

use App\Repository\NotificationRepository;
use PHPUnit\Framework\TestCase;

class NotificationRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(NotificationRepository::class));
    }
}
