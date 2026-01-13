<?php

namespace App\Tests\Repository;

use App\Repository\ContactMessageRepository;
use PHPUnit\Framework\TestCase;

class ContactMessageRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $this->assertTrue(class_exists(ContactMessageRepository::class));
    }
}
