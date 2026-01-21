<?php

namespace App\Tests\Integration;

use App\Entity\Benevole;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BenevoleIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testCreateAndPersistBenevole(): void
    {
        $benevole = new Benevole();
        $benevole->setRemarque('Test Benevole');
        $benevole->setActif(true);

        $this->entityManager->persist($benevole);
        $this->entityManager->flush();

        $this->assertNotNull($benevole->getId());
        $this->assertTrue($benevole->isActif());
    }

    public function testRetrieveBenevoleFromDatabase(): void
    {
        $benevole = new Benevole();
        $benevole->setRemarque('Benevole to Retrieve');
        $benevole->setActif(true);

        $this->entityManager->persist($benevole);
        $this->entityManager->flush();

        $id = $benevole->getId();
        $this->entityManager->clear();

        $retrievedBenevole = $this->entityManager->find(Benevole::class, $id);
        $this->assertNotNull($retrievedBenevole);
        $this->assertEquals('Benevole to Retrieve', $retrievedBenevole->getRemarque());
    }

    public function testUpdateBenevole(): void
    {
        $benevole = new Benevole();
        $benevole->setRemarque('Original Remark');
        $benevole->setActif(true);

        $this->entityManager->persist($benevole);
        $this->entityManager->flush();

        $benevole->setRemarque('Updated Remark');
        $this->entityManager->flush();

        $this->assertEquals('Updated Remark', $benevole->getRemarque());
    }

    public function testDeleteBenevole(): void
    {
        $benevole = new Benevole();
        $benevole->setRemarque('To Delete');
        $benevole->setActif(true);

        $this->entityManager->persist($benevole);
        $this->entityManager->flush();

        $id = $benevole->getId();
        $this->entityManager->remove($benevole);
        $this->entityManager->flush();

        $deletedBenevole = $this->entityManager->find(Benevole::class, $id);
        $this->assertNull($deletedBenevole);
    }
}
