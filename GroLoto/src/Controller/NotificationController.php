<?php
namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    public function index(NotificationRepository $notificationRepo): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->findByUser($user);
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notifications/count', name: 'app_notifications_count')]
    public function count(NotificationRepository $notificationRepo): JsonResponse
    {
        $user = $this->getUser();
        $count = $notificationRepo->countUnreadByUser($user);
        return new JsonResponse(['count' => $count]);
    }

    #[Route('/notifications/unread', name: 'app_notifications_unread')]
    public function unread(NotificationRepository $notificationRepo): JsonResponse
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->findUnreadByUser($user);
        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'lien' => $notification->getLien(),
                'createdAt' => $notification->getCreatedAt()->format('d/m/Y H:i'),
            ];
        }
        return new JsonResponse($data);
    }

    #[Route('/notifications/{id}/marquer-lue', name: 'app_notification_mark_read', methods: ['POST'])]
    public function markAsRead(Notification $notification, EntityManagerInterface $em): JsonResponse
    {
        if ($notification->getDestinataire() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }
        $notification->setLue(true);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route('/notifications/marquer-toutes-lues', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(NotificationRepository $notificationRepo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $notificationRepo->markAllAsReadByUser($user);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route('/notifications/mark-conversation-read/{conversationId}', name: 'app_notification_mark_conversation_read', methods: ['POST'])]
    public function markConversationAsRead(int $conversationId, NotificationRepository $notificationRepo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        
        // Marquer toutes les notifications de type contact qui contiennent cet ID de conversation dans le lien
        $notifications = $notificationRepo->createQueryBuilder('n')
            ->where('n.destinataire = :user')
            ->andWhere('n.lue = false')
            ->andWhere('n.type IN (:types)')
            ->andWhere('n.lien LIKE :convId')
            ->setParameter('user', $user)
            ->setParameter('types', ['contact', 'reponse_contact'])
            ->setParameter('convId', '%conv=' . $conversationId . '%')
            ->getQuery()
            ->getResult();
        
        foreach ($notifications as $notification) {
            $notification->setLue(true);
        }
        
        $em->flush();
        
        return new JsonResponse(['success' => true, 'marked' => count($notifications)]);
    }
}