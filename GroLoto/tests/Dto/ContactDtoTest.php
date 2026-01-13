<?php

namespace App\Tests\Dto;

use App\Dto\ContactDto;
use PHPUnit\Framework\TestCase;

class ContactDtoTest extends TestCase
{
    public function testGetSetNom(): void
    {
        $dto = new ContactDto();
        
        $this->assertNull($dto->getNom());
        
        $nom = 'Jean Dupont';
        $result = $dto->setNom($nom);
        $this->assertSame($dto, $result);
        $this->assertEquals($nom, $dto->getNom());
    }

    public function testGetSetEmail(): void
    {
        $dto = new ContactDto();
        
        $this->assertNull($dto->getEmail());
        
        $email = 'test@example.com';
        $result = $dto->setEmail($email);
        $this->assertSame($dto, $result);
        $this->assertEquals($email, $dto->getEmail());
    }

    public function testGetSetSujet(): void
    {
        $dto = new ContactDto();
        
        $this->assertNull($dto->getSujet());
        
        $sujet = 'Question importante';
        $result = $dto->setSujet($sujet);
        $this->assertSame($dto, $result);
        $this->assertEquals($sujet, $dto->getSujet());
    }

    public function testSetSujetNull(): void
    {
        $dto = new ContactDto();
        $dto->setSujet('Test');
        
        $result = $dto->setSujet(null);
        $this->assertSame($dto, $result);
        $this->assertNull($dto->getSujet());
    }

    public function testGetSetMessage(): void
    {
        $dto = new ContactDto();
        
        $this->assertNull($dto->getMessage());
        
        $message = 'Ceci est un message de test';
        $result = $dto->setMessage($message);
        $this->assertSame($dto, $result);
        $this->assertEquals($message, $dto->getMessage());
    }

    public function testCompleteDto(): void
    {
        $dto = new ContactDto();
        
        $dto->setNom('Jean Dupont')
            ->setEmail('jean@example.com')
            ->setSujet('Question')
            ->setMessage('Message de test');
        
        $this->assertEquals('Jean Dupont', $dto->getNom());
        $this->assertEquals('jean@example.com', $dto->getEmail());
        $this->assertEquals('Question', $dto->getSujet());
        $this->assertEquals('Message de test', $dto->getMessage());
    }
}
