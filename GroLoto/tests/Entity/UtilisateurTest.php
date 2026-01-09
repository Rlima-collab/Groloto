<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Utilisateur;
use App\Entity\Role;

class UtilisateurTest extends TestCase
{
    public function testGetRolesMapping()
    {
        $u = new Utilisateur();
        $this->assertEquals(['ROLE_USER'], $u->getRoles());

        $role = new Role();
        $role->setNom('admin');
        $u->setRole($role);
        $this->assertContains('ROLE_ADMIN', $u->getRoles());

        $role->setNom('benevole');
        $this->assertContains('ROLE_BENEVOLE', $u->getRoles());
    }
}
