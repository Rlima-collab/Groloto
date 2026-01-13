<?php

namespace App\Tests\Entity;

use App\Entity\ContactMessage;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class ContactMessageTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $message = new ContactMessage();
        $this->assertInstanceOf(\DateTimeInterface::class, $message->getCreatedAt());
    }

    public function testGetSetId(): void
    {
        $message = new ContactMessage();
        $this->assertNull($message->getId());
    }

    public function testGetSetNom(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getNom());
        
        $nom = 'Jean Dupont';
        $result = $message->setNom($nom);
        $this->assertSame($message, $result);
        $this->assertEquals($nom, $message->getNom());
    }

    public function testGetSetEmail(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getEmail());
        
        $email = 'test@example.com';
        $result = $message->setEmail($email);
        $this->assertSame($message, $result);
        $this->assertEquals($email, $message->getEmail());
    }

    public function testGetSetDestinataire(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getDestinataire());
        
        $destinataire = 'admin';
        $result = $message->setDestinataire($destinataire);
        $this->assertSame($message, $result);
        $this->assertEquals($destinataire, $message->getDestinataire());
    }

    public function testGetSetMessage(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getMessage());
        
        $text = 'Message de test';
        $result = $message->setMessage($text);
        $this->assertSame($message, $result);
        $this->assertEquals($text, $message->getMessage());
    }

    public function testGetSetCreatedAt(): void
    {
        $message = new ContactMessage();
        $date = new \DateTime('2025-01-15 10:00:00');
        
        $result = $message->setCreatedAt($date);
        $this->assertSame($message, $result);
        $this->assertSame($date, $message->getCreatedAt());
    }

    public function testIsSetLu(): void
    {
        $message = new ContactMessage();
        
        $this->assertFalse($message->isLu());
        
        $result = $message->setLu(true);
        $this->assertSame($message, $result);
        $this->assertTrue($message->isLu());
    }

    public function testGetSetReponduPar(): void
    {
        $message = new ContactMessage();
        $utilisateur = new Utilisateur();
        
        $this->assertNull($message->getReponduPar());
        
        $result = $message->setReponduPar($utilisateur);
        $this->assertSame($message, $result);
        $this->assertSame($utilisateur, $message->getReponduPar());
    }

    public function testGetSetReponse(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getReponse());
        
        $reponse = 'Réponse au message';
        $result = $message->setReponse($reponse);
        $this->assertSame($message, $result);
        $this->assertEquals($reponse, $message->getReponse());
    }

    public function testGetSetReponduLe(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getReponduLe());
        
        $date = new \DateTime('2025-01-16 14:00:00');
        $result = $message->setReponduLe($date);
        $this->assertSame($message, $result);
        $this->assertSame($date, $message->getReponduLe());
    }

    public function testGetSetParentId(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getParentId());
        
        $result = $message->setParentId(42);
        $this->assertSame($message, $result);
        $this->assertEquals(42, $message->getParentId());
    }

    public function testIsSetCloturee(): void
    {
        $message = new ContactMessage();
        
        $this->assertFalse($message->isCloturee());
        
        $result = $message->setCloturee(true);
        $this->assertSame($message, $result);
        $this->assertTrue($message->isCloturee());
    }

    public function testGetSetMasqueePour(): void
    {
        $message = new ContactMessage();
        
        $this->assertNull($message->getMasqueePour());
        
        $masquee = 'user123';
        $result = $message->setMasqueePour($masquee);
        $this->assertSame($message, $result);
        $this->assertEquals($masquee, $message->getMasqueePour());
    }
}
