<?php

namespace App\Tests\Integration;

use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UtilisateurIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ?Role $testRole = null;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        
        // Create or get test role
        $this->testRole = $this->entityManager->getRepository(Role::class)->findOneBy([]);
        if (!$this->testRole) {
            $this->testRole = new Role();
            $this->testRole->setLibelle('ROLE_USER');
            $this->entityManager->persist($this->testRole);
            $this->entityManager->flush();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testCreateUtilisateur(): void
    {
        $user = new Utilisateur();
        $user->setEmail('user-' . time() . '@test.com');
        $user->setMotDePasse('hashed_password');
        $user->setPrenom('John');
        $user->setNom('Doe');
        if ($this->testRole) {
            $user->setRole($this->testRole);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->assertNotNull($user->getId());
        $this->assertStringEndsWith('@test.com', $user->getEmail());
    }

    public function testUtilisateurHasData(): void
    {
        $user = new Utilisateur();
        $user->setEmail('admin-' . time() . '@test.com');
        $user->setMotDePasse('hashed_password');
        $user->setPrenom('Admin');
        $user->setNom('User');
        if ($this->testRole) {
            $user->setRole($this->testRole);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->assertEquals('Admin', $user->getPrenom());
        $this->assertEquals('User', $user->getNom());
    }

    public function testFindUtilisateurByEmail(): void
    {
        $user = new Utilisateur();
        $user->setEmail('findme-' . time() . '@test.com');
        $user->setMotDePasse('hashed_password');
        $user->setPrenom('Find');
        $user->setNom('Me');
        if ($this->testRole) {
            $user->setRole($this->testRole);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundUser = $this->entityManager->getRepository(Utilisateur::class)
            ->findOneBy(['email' => 'findme@test.com']);

        $this->assertNotNull($foundUser);
        $this->assertEquals('findme@test.com', $foundUser->getEmail());
    }
}
