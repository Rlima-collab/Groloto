<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notificationRepository = $this->createMock(NotificationRepository::class);
        
        $this->notificationService = new NotificationService(
            $this->entityManager,
            $this->notificationRepository
        );
    }

    public function testCreateNotification(): void
    {
        $utilisateur = new Utilisateur();
        $type = 'info';
        $message = 'Test message';
        $lien = '/test/path';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Notification::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $notification = $this->notificationService->createNotification(
            $utilisateur,
            $type,
            $message,
            $lien
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame($utilisateur, $notification->getDestinataire());
        $this->assertEquals($type, $notification->getType());
        $this->assertEquals($message, $notification->getMessage());
        $this->assertEquals($lien, $notification->getLien());
        $this->assertFalse($notification->isLue());
    }

    public function testCreateNotificationWithoutLien(): void
    {
        $utilisateur = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $notification = $this->notificationService->createNotification(
            $utilisateur,
            'info',
            'Test'
        );

        $this->assertNull($notification->getLien());
    }

    public function testNotifyBenevoleDemandeAcceptee(): void
    {
        $benevole = new Utilisateur();
        $tacheNom = 'Tâche Test';

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeAcceptee($benevole, $tacheNom);
        
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testNotifyBenevoleDemandeRefusee(): void
    {
        $benevole = new Utilisateur();
        $tacheNom = 'Tâche Test';
        $raison = 'Manque de compétences';

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeRefusee($benevole, $tacheNom, $raison);
        
        $this->assertTrue(true);
    }

    public function testNotifyBenevoleDemandeRefuseeSansRaison(): void
    {
        $benevole = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeRefusee($benevole, 'Tâche');
        
        $this->assertTrue(true);
    }

    public function testNotifyBenevoleAffectation(): void
    {
        $benevole = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleAffectation($benevole, 'Tâche Test');
        
        $this->assertTrue(true);
    }

    public function testNotifyAdminDemandeMecene(): void
    {
        $admin = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyAdminDemandeMecene($admin, 'Mécène Test', 'Événement Test');
        
        $this->assertTrue(true);
    }

    public function testNotifyMeceneDemandeAcceptee(): void
    {
        $mecene = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyMeceneDemandeAcceptee($mecene, 'Événement Test');
        
        $this->assertTrue(true);
    }

    public function testNotifyMeceneDemandeRefusee(): void
    {
        $mecene = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyMeceneDemandeRefusee($mecene, 'Événement Test', 'Budget insuffisant');
        
        $this->assertTrue(true);
    }

    public function testNotifyAdminDemandeAnnulation(): void
    {
        $admin = new Utilisateur();
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyAdminDemandeAnnulation($admin, 'Jean', 'Tâche Test');
        
        $this->assertTrue(true);
    }
}
