<?php

namespace App\Tests\Service;

use App\Service\MessagePublisher;
use PHPUnit\Framework\TestCase;

class MessagePublisherTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(MessagePublisher::class));
    }
}
