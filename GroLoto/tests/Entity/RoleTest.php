<?php

namespace App\Tests\Entity;

use App\Entity\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testGetSetId(): void
    {
        $role = new Role();
        $this->assertNull($role->getId());
    }

    public function testGetSetNom(): void
    {
        $role = new Role();
        
        $this->assertNull($role->getNom());
        
        $nom = 'ROLE_ADMIN';
        $result = $role->setNom($nom);
        $this->assertSame($role, $result);
        $this->assertEquals($nom, $role->getNom());
    }

    public function testGetSetDescription(): void
    {
        $role = new Role();
        
        $this->assertNull($role->getDescription());
        
        $description = 'Administrateur du système';
        $result = $role->setDescription($description);
        $this->assertSame($role, $result);
        $this->assertEquals($description, $role->getDescription());
    }

    public function testSetDescriptionNull(): void
    {
        $role = new Role();
        $role->setDescription('Test');
        
        $result = $role->setDescription(null);
        $this->assertSame($role, $result);
        $this->assertNull($role->getDescription());
    }
}
