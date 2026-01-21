<?php
namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ContactMessageController;
use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ContactMessageControllerTest extends TestCase
{
    private function makeEntityManagerWithUser(?Utilisateur $user = null): EntityManagerInterface
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturnCallback(function ($criteria) use ($user) {
            $email = $criteria['email'] ?? null;
            if (!$email) return null;
            if ($user && $user->getEmail() === $email) return $user;
            return null;
        });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        return $em;
    }

    public function testExtractSubject()
    {
        $em = $this->makeEntityManagerWithUser();
        $controller = new ContactMessageController($em);

        $ref = new \ReflectionClass($controller);
        $m = $ref->getMethod('extractSubject');
        $m->setAccessible(true);

        $this->assertEquals('Sujet', $m->invoke($controller, '[Sujet] contenu'));
        $this->assertEquals('Votre message', $m->invoke($controller, 'aucun sujet ici'));
    }

    public function testGetUserRoleAndProfileImage()
    {
        $user = new Utilisateur();
        $user->setEmail('user@example.com');
        $role = new Role();
        $role->setNom('mecene');
        $user->setRole($role);
        $user->setPrenom('Jean');
        $user->setNom('Dupont');
        $user->setProfileImage('avatar.png');

        $em = $this->makeEntityManagerWithUser($user);
        $controller = new ContactMessageController($em);

        $ref = new \ReflectionClass($controller);

        $mRole = $ref->getMethod('getUserRole');
        $mRole->setAccessible(true);

        $this->assertEquals('Inconnu', $mRole->invoke($controller, null));
        $this->assertEquals('Admin', $mRole->invoke($controller, 'ROLE_ADMIN'));
        $this->assertEquals('Mécène', $mRole->invoke($controller, 'user@example.com'));
        $this->assertEquals('Externe', $mRole->invoke($controller, 'unknown@ext.test'));

        $mImg = $ref->getMethod('getUserProfileImage');
        $mImg->setAccessible(true);

        $this->assertNull($mImg->invoke($controller, null));
        $this->assertEquals('avatar.png', $mImg->invoke($controller, 'user@example.com'));
        $this->assertNull($mImg->invoke($controller, 'other@nope.test'));
    }
}
