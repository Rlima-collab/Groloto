<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Créer une notification pour un utilisateur
     */
    public function createNotification(
        Utilisateur $utilisateur,
        string $type,
        string $message,
        ?string $lien = null
    ): Notification {
        $notification = new Notification();
        $notification->setDestinataire($utilisateur);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setLien($lien);
        $notification->setLue(false);
        // createdAt is set in constructor

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Notifier l'admin qu'un bénévole a fait une demande de tâche
     */
    public function notifyAdminDemandeTache(Utilisateur $admin, string $benevoleNom, string $tacheNom): void
    {
        $this->createNotification(
            $admin,
            'demande_tache',
            "{$benevoleNom} souhaite rejoindre la tâche \"{$tacheNom}\"",
            '/admin/demandes-taches'
        );
    }

    /**
     * Notifier un bénévole que sa demande a été acceptée
     */
    public function notifyBenevoleDemandeAcceptee(Utilisateur $benevole, string $tacheNom): void
    {
        $this->createNotification(
            $benevole,
            'acceptation_tache',
            "Votre demande pour la tâche \"{$tacheNom}\" a été acceptée ! Consultez vos demandes pour plus de détails.",
            '/benevole/taches-disponibles'
        );
    }

    /**
     * Notifier un bénévole que sa demande a été refusée
     */
    public function notifyBenevoleDemandeRefusee(Utilisateur $benevole, string $tacheNom, ?string $raison = null): void
    {
        $message = "Votre demande pour la tâche \"{$tacheNom}\" a été refusée.";
        if ($raison) {
            $message .= " Motif : {$raison}";
        }
        
        $this->createNotification(
            $benevole,
            'refus_tache',
            $message,
            '/benevole/taches-disponibles'
        );
    }

    /**
     * Notifier un bénévole qu'il a été affecté à une tâche par l'admin
     */
    public function notifyBenevoleAffectation(Utilisateur $benevole, string $tacheNom): void
    {
        $this->createNotification(
            $benevole,
            'affectation_tache',
            "Vous avez été affecté(e) à la tâche \"{$tacheNom}\"",
            '/benevoles'
        );
    }

    /**
     * Notifier l'admin qu'un mécène a demandé à rejoindre un événement
     */
    public function notifyAdminDemandeMecene(Utilisateur $admin, string $meceneNom, string $evenementNom): void
    {
        $this->createNotification(
            $admin,
            'demande_mecene',
            "{$meceneNom} souhaite participer à l'événement \"{$evenementNom}\"",
            '/mecenes'
        );
    }

    /**
     * Notifier un mécène que sa demande a été acceptée
     */
    public function notifyMeceneDemandeAcceptee(Utilisateur $mecene, string $evenementNom): void
    {
        $this->createNotification(
            $mecene,
            'acceptation_mecene',
            "Votre demande pour l'événement \"{$evenementNom}\" a été acceptée !",
            '/mecenes'
        );
    }

    /**
     * Notifier un mécène que sa demande a été refusée
     */
    public function notifyMeceneDemandeRefusee(Utilisateur $mecene, string $evenementNom, ?string $raison = null): void
    {
        $message = "Votre demande pour l'événement \"{$evenementNom}\" a été refusée.";
        if ($raison) {
            $message .= " Motif : {$raison}";
        }
        
        $this->createNotification(
            $mecene,
            'refus_mecene',
            $message,
            '/mecenes'
        );
    }

    /**
     * Notifier l'admin qu'un bénévole demande l'annulation d'une tâche
     */
    public function notifyAdminDemandeAnnulation(Utilisateur $admin, string $benevoleNom, string $tacheNom): void
    {
        $this->createNotification(
            $admin,
            'demande_annulation',
            "{$benevoleNom} souhaite annuler son affectation à la tâche \"{$tacheNom}\"",
            '/admin/demandes-annulations'
        );
    }

    /**
     * Notifier un bénévole que sa demande d'annulation a été acceptée
     */
    public function notifyBenevoleAnnulationAcceptee(Utilisateur $benevole, string $tacheNom): void
    {
        $this->createNotification(
            $benevole,
            'acceptation_annulation',
            "Votre demande d'annulation pour la tâche \"{$tacheNom}\" a été acceptée.",
            '/benevoles'
        );
    }

    /**
     * Notifier un bénévole que sa demande d'annulation a été refusée
     */
    public function notifyBenevoleAnnulationRefusee(Utilisateur $benevole, string $tacheNom, ?string $raison = null): void
    {
        $message = "Votre demande d'annulation pour la tâche \"{$tacheNom}\" a été refusée.";
        if ($raison) {
            $message .= " Motif : {$raison}";
        }
        
        $this->createNotification(
            $benevole,
            'refus_annulation',
            $message,
            '/benevoles'
        );
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setLue(true);
        $this->entityManager->flush();
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(Utilisateur $utilisateur): void
    {
        $this->notificationRepository->markAllAsReadByUser($utilisateur);
    }
}
