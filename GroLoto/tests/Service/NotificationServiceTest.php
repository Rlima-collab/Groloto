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

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeAcceptee($benevole, $tacheNom);
        
        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('acceptation_tache', $captured->getType());
        $this->assertStringContainsString($tacheNom, $captured->getMessage());
        $this->assertSame('/benevole/taches-disponibles', $captured->getLien());
    }

    public function testNotifyBenevoleDemandeRefusee(): void
    {
        $benevole = new Utilisateur();
        $tacheNom = 'Tâche Test';
        $raison = 'Manque de compétences';

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeRefusee($benevole, $tacheNom, $raison);
        
        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('refus_tache', $captured->getType());
        $this->assertStringContainsString($tacheNom, $captured->getMessage());
        $this->assertStringContainsString('Motif', $captured->getMessage());
    }

    public function testNotifyBenevoleDemandeRefuseeSansRaison(): void
    {
        $benevole = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleDemandeRefusee($benevole, 'Tâche');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('refus_tache', $captured->getType());
        $this->assertStringNotContainsString('Motif', $captured->getMessage());
    }

    public function testNotifyBenevoleAffectation(): void
    {
        $benevole = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyBenevoleAffectation($benevole, 'Tâche Test');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('affectation_tache', $captured->getType());
        $this->assertSame('/benevoles', $captured->getLien());
    }

    public function testNotifyAdminDemandeMecene(): void
    {
        $admin = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyAdminDemandeMecene($admin, 'Mécène Test', 'Événement Test');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('demande_mecene', $captured->getType());
        $this->assertSame('/mecenes', $captured->getLien());
    }

    public function testNotifyMeceneDemandeAcceptee(): void
    {
        $mecene = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyMeceneDemandeAcceptee($mecene, 'Événement Test');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('acceptation_mecene', $captured->getType());
    }

    public function testNotifyMeceneDemandeRefusee(): void
    {
        $mecene = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyMeceneDemandeRefusee($mecene, 'Événement Test', 'Budget insuffisant');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('refus_mecene', $captured->getType());
        $this->assertStringContainsString('Motif', $captured->getMessage());
    }

    public function testNotifyAdminDemandeAnnulation(): void
    {
        $admin = new Utilisateur();

        $captured = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured = $notification;
            });
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->notificationService->notifyAdminDemandeAnnulation($admin, 'Jean', 'Tâche Test');

        $this->assertInstanceOf(Notification::class, $captured);
        $this->assertSame('demande_annulation', $captured->getType());
        $this->assertSame('/admin/demandes-annulations', $captured->getLien());
    }

    public function testNotifyBenevolePropositionAndAssignationAndRetraitAndSuppression(): void
    {
        $benevole = new Utilisateur();

        $captured = [];
        $this->entityManager->expects($this->exactly(4))
            ->method('persist')
            ->willReturnCallback(function (Notification $notification) use (&$captured): void {
                $captured[] = $notification;
            });
        $this->entityManager->expects($this->exactly(4))->method('flush');

        $this->notificationService->notifyBenevoleProposition($benevole, 'T1');
        $this->notificationService->notifyBenevoleAssigne($benevole, 'T2');
        $this->notificationService->notifyBenevoleRetireDeTache($benevole, 'T3');
        $this->notificationService->notifyBenevoleTacheSupprimee($benevole, 'T4');

        $this->assertCount(4, $captured);
        $this->assertSame('proposition_tache', $captured[0]->getType());
        $this->assertSame('assignation_tache', $captured[1]->getType());
        $this->assertSame('retrait_tache', $captured[2]->getType());
        $this->assertSame('suppression_tache', $captured[3]->getType());
    }

    public function testMarkAsReadAndMarkAllAsRead(): void
    {
        $notification = new Notification();
        $notification->setLue(false);

        $this->entityManager->expects($this->once())->method('flush');

        $this->notificationService->markAsRead($notification);
        $this->assertTrue($notification->isLue());

        $user = new Utilisateur();
        $this->notificationRepository->expects($this->once())
            ->method('markAllAsReadByUser')
            ->with($user);

        $this->notificationService->markAllAsRead($user);
    }
}
