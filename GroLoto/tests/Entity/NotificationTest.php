<?php

namespace App\Tests\Entity;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $notification = new Notification();
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getCreatedAt());
    }

    public function testGetSetId(): void
    {
        $notification = new Notification();
        $this->assertNull($notification->getId());
    }

    public function testGetSetDestinataire(): void
    {
        $notification = new Notification();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($notification->getDestinataire());
        
        $result = $notification->setDestinataire($utilisateur);
        $this->assertSame($notification, $result);
        $this->assertSame($utilisateur, $notification->getDestinataire());
    }

    public function testGetSetType(): void
    {
        $notification = new Notification();
        
        $this->assertNull($notification->getType());
        
        $type = 'info';
        $result = $notification->setType($type);
        $this->assertSame($notification, $result);
        $this->assertEquals($type, $notification->getType());
    }

    public function testGetSetMessage(): void
    {
        $notification = new Notification();
        
        $this->assertNull($notification->getMessage());
        
        $message = 'Test notification';
        $result = $notification->setMessage($message);
        $this->assertSame($notification, $result);
        $this->assertEquals($message, $notification->getMessage());
    }

    public function testGetSetLien(): void
    {
        $notification = new Notification();
        
        $this->assertNull($notification->getLien());
        
        $lien = '/path/to/page';
        $result = $notification->setLien($lien);
        $this->assertSame($notification, $result);
        $this->assertEquals($lien, $notification->getLien());
    }

    public function testIsSetLue(): void
    {
        $notification = new Notification();
        
        $this->assertFalse($notification->isLue());
        
        $result = $notification->setLue(true);
        $this->assertSame($notification, $result);
        $this->assertTrue($notification->isLue());
    }

    public function testGetSetCreatedAt(): void
    {
        $notification = new Notification();
        $date = new \DateTime('2025-01-15 10:00:00');
        
        $result = $notification->setCreatedAt($date);
        $this->assertSame($notification, $result);
        $this->assertSame($date, $notification->getCreatedAt());
    }
}
