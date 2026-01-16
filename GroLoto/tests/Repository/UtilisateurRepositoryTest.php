<?php

namespace App\Tests\Repository;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UtilisateurRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->getMockBuilder(UtilisateurRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    public function testRepositoryExists(): void
    {
        $this->assertInstanceOf(UtilisateurRepository::class, $this->repository);
    }
}
