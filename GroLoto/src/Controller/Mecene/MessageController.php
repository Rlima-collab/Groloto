<?php

namespace App\Controller\Mecene;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/mecene/messages')]
#[IsGranted('ROLE_MECENE')]
class MessageController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'mecene_messages')]
    public function index(): Response
    {
        $user = $this->getUser();
        $email = $user?->getEmail();

        $messages = [];
        if ($email) {
            // Récupérer les messages racines (parent_id IS NULL) où l'utilisateur est soit destinataire, soit expéditeur
            // ET qui ne sont pas masqués pour cet utilisateur
            $messages = $this->em->getRepository(ContactMessage::class)
                ->createQueryBuilder('cm')
                ->where('cm.parent_id IS NULL')
                ->andWhere('cm.destinataire = :email OR cm.email = :email')
                ->andWhere('cm.masqueePour IS NULL OR cm.masqueePour NOT LIKE :emailPattern')
                ->setParameter('email', $email)
                ->setParameter('emailPattern', '%' . $email . '%')
                ->orderBy('cm.createdAt', 'DESC')
                ->getQuery()
                ->getResult();

            // Pour chaque message racine, charger la conversation complète et déterminer le rôle
            foreach ($messages as $msg) {
                $msg->conversation = $this->getConversationThread($msg->getId());
                
                // Déterminer le dernier message de la conversation
                if (!empty($msg->conversation)) {
                    $lastReply = end($msg->conversation);
                    $msg->lastMessage = $lastReply->getMessage();
                    $msg->lastMessageDate = $lastReply->getCreatedAt();
                } else {
                    $msg->lastMessage = $msg->getMessage();
                    $msg->lastMessageDate = $msg->getCreatedAt();
                }
                
                // Déterminer le rôle de l'interlocuteur
                $otherEmail = ($msg->getEmail() === $email) ? $msg->getDestinataire() : $msg->getEmail();
                $msg->otherUserRole = $this->getUserRole($otherEmail);
            }
        }

        // trouver un admin (pour affichage destinataire dans le compose)
        $adminUser = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom IN (:roles)')
            ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $adminEmail = $adminUser ? $adminUser->getEmail() : 'contact@groloto.com';

        return $this->render('mecene/message/messages.html.twig', [
            'messages' => $messages,
            'adminEmail' => $adminEmail,
        ]);
    }

    /**
     * Détermine le rôle d'un utilisateur par son email
     */
    private function getUserRole(?string $email): string
    {
        if (!$email) return 'Inconnu';
        
        $user = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->findOneBy(['email' => $email]);
        
        if (!$user) return 'Externe';
        
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) return 'Admin';
        if (in_array('ROLE_MECENE', $roles)) return 'Mécène';
        if (in_array('ROLE_BENEVOLE', $roles)) return 'Bénévole';
        
        return 'Utilisateur';
    }

    /**
     * Récupère tous les messages d'un fil de discussion
     */
    private function getConversationThread(int $rootMessageId): array
    {
        // Récupérer tous les messages ayant ce parent_id
        return $this->em->getRepository(ContactMessage::class)
            ->createQueryBuilder('cm')
            ->where('cm.parent_id = :parentId')
            ->setParameter('parentId', $rootMessageId)
            ->orderBy('cm.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    #[Route('/envoyes', name: 'mecene_messages_sent')]
    public function sent(): Response
    {
        $user = $this->getUser();
        $email = $user?->getEmail();

        $messages = [];
        if ($email) {
            // Seulement les messages ENVOYÉS (email = mon email, destinataire rempli)
            $messages = $this->em->getRepository(ContactMessage::class)
                ->findBy(['email' => $email], ['createdAt' => 'DESC']);
        }

        // admin email pour le popup
        $adminUser = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom IN (:roles)')
            ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $adminEmail = $adminUser ? $adminUser->getEmail() : 'contact@groloto.com';

        return $this->render('mecene/message/messages.html.twig', [
            'messages' => $messages,
            'sent_view' => true,
            'adminEmail' => $adminEmail,
        ]);
    }

    #[Route('/nouveau', name: 'mecene_message_new', methods: ['POST'])]
    public function new(Request $request, MailerInterface $mailer): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête non autorisée'], 403);
        }

        $messageText = trim($request->request->get('message'));
        $subject = trim($request->request->get('subject'));
        if (!$messageText) {
            return $this->json(['success' => false, 'error' => 'Message vide'], 400);
        }

        $user = $this->getUser();
        $nom = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? '')) ?: $user->getEmail();
        $email = $user->getEmail();

        // Récupérer l'email d'un admin
        $adminUser = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom = :role')
            ->setParameter('role', 'admin')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $adminEmail = $adminUser ? $adminUser->getEmail() : 'contact@groloto.com';

        // Créer un nouveau message racine (nouvelle conversation)
        $cm = new ContactMessage();
        $cm->setNom($nom)
            ->setEmail($email)
            ->setDestinataire($adminEmail)
            ->setMessage($messageText);

        $this->em->persist($cm);
        $this->em->flush();

        // Créer une notification pour tous les admins
        try {
            $admins = $this->em->getRepository(\App\Entity\Utilisateur::class)
                ->createQueryBuilder('u')
                ->join('u.role', 'r')
                ->where('r.nom IN (:roles)')
                ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
                ->getQuery()
                ->getResult();

            foreach ($admins as $adm) {
                $notification = new \App\Entity\Notification();
                $notification->setDestinataire($adm)
                    ->setType('contact')
                    ->setMessage(sprintf('Nouveau message de %s', $nom))
                    ->setLien('/admin/messages?conv=' . $cm->getId())
                    ->setLue(false);
                $this->em->persist($notification);
            }
            $this->em->flush();
        } catch (\Exception $e) {
            // Ne pas bloquer l'envoi si la notification échoue
        }

        try {
            $emailObj = (new Email())
                ->from($email)
                ->to($adminEmail)
                ->subject($subject ?: 'Message depuis la messagerie')
                ->html(nl2br(htmlspecialchars($messageText)));

            $mailer->send($emailObj);
        } catch (\Exception $e) {
            // ignore mail failure for now
        }

        return $this->json(['success' => true, 'message' => 'Message envoyé !', 'conversationId' => $cm->getId()]);
    }

    #[Route('/{id}/delete', name: 'mecene_message_delete', methods: ['POST'])]
    public function delete(ContactMessage $message, Request $request): Response
    {
        $isAjax = $request->isXmlHttpRequest();
        
        // sécurité : n'autoriser que le propriétaire (email)
        $user = $this->getUser();
        if ($user->getEmail() !== $message->getEmail() && $user->getEmail() !== $message->getDestinataire()) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Action non autorisée'], 403);
            }
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('mecene_messages');
        }

        // Clôturer la conversation avant de la masquer
        $message->setCloturee(true);

        // Ne pas supprimer, mais masquer pour l'utilisateur
        $message->masquerPour($user->getEmail());
        $this->em->flush();
        
        if ($isAjax) {
            return $this->json(['success' => true, 'message' => 'Conversation supprimée']);
        }
        
        $this->addFlash('success', 'Message supprimé.');
        return $this->redirectToRoute('mecene_messages');
    }

    #[Route('/{id}/cloturer', name: 'mecene_message_close', methods: ['POST'])]
    public function cloturer(ContactMessage $message, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getEmail() !== $message->getEmail() && $user->getEmail() !== $message->getDestinataire()) {
            return $this->json(['success' => false, 'error' => 'Action non autorisée'], 403);
        }

        $message->setCloturee(true);
        $this->em->flush();

        return $this->json(['success' => true, 'message' => 'Conversation clôturée']);
    }

    #[Route('/{id}/repondre', name: 'mecene_message_reply', methods: ['POST'])]
    public function reply(Request $request, ContactMessage $message, MailerInterface $mailer): JsonResponse
    {
        $isAjax = $request->isXmlHttpRequest();
        
        $currentUser = $this->getUser();
        $currentEmail = $currentUser->getEmail();

        // Vérifier si la conversation est masquée par l'autre utilisateur
        $otherEmail = ($message->getEmail() === $currentEmail) ? $message->getDestinataire() : $message->getEmail();
        if ($message->isMasqueePour($otherEmail)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Cette conversation a été supprimée par l\'autre utilisateur.'], 410);
            }
            $this->addFlash('error', 'Cette conversation a été supprimée par l\'autre utilisateur.');
            return $this->redirectToRoute('mecene_messages');
        }
        
        // Vérifier si la conversation est clôturée
        if ($message->isCloturee()) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Cette conversation est clôturée.'], 403);
            }
            $this->addFlash('error', 'Cette conversation est clôturée.');
            return $this->redirectToRoute('mecene_messages');
        }
        
        $reponse = trim($request->request->get('reponse')) ?: trim($request->request->get('message'));
        if (!$reponse) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'La réponse est vide.'], 400);
            }
            $this->addFlash('error', 'La réponse est vide.');
            return $this->redirectToRoute('mecene_messages');
        }

        $user = $this->getUser();
        $nom = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? '')) ?: $user->getEmail();
        
        // Récupérer l'email d'un admin
        $adminUser = $this->em->getRepository(\App\Entity\Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom IN (:roles)')
            ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $adminEmail = $adminUser ? $adminUser->getEmail() : 'contact@groloto.com';

        // Extraire le sujet du message original
        $subject = 'Re: Votre message';
        if (preg_match('/^\[([^\]]+)\]/', $message->getMessage(), $matches)) {
            $subject = 'Re: ' . $matches[1];
        }
        
        // Déterminer le message racine (parent_id)
        $rootParentId = $message->getParentId() ?? $message->getId();
        
        $newMessage = new ContactMessage();
        $newMessage->setNom($nom)
            ->setEmail($user->getEmail())
            ->setDestinataire($adminEmail)
            ->setMessage($reponse)
            ->setParentId($rootParentId); // Lien vers le message racine

        $this->em->persist($newMessage);
        $this->em->flush();

        // Créer une notification pour tous les admins
        try {
            $admins = $this->em->getRepository(\App\Entity\Utilisateur::class)
                ->createQueryBuilder('u')
                ->join('u.role', 'r')
                ->where('r.nom IN (:roles)')
                ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
                ->getQuery()
                ->getResult();

            foreach ($admins as $adm) {
                $notification = new \App\Entity\Notification();
                $notification->setDestinataire($adm)
                    ->setType('contact')
                    ->setMessage(sprintf('Nouvelle réponse de %s', $nom))
                    ->setLien('/admin/messages?conv=' . $rootParentId)
                    ->setLue(false);
                $this->em->persist($notification);
            }
            $this->em->flush();
        } catch (\Exception $e) {
            // Ne pas bloquer l'envoi si la notification échoue
        }

        try {
            $email = (new Email())
                ->from($user->getEmail())
                ->to($adminEmail)
                ->subject($subject)
                ->html("
                    <h3>Message original :</h3>
                    <blockquote style='border-left:3px solid #ccc; padding-left:10px; margin: 10px 0;'>
                        <em>{$message->getMessage()}</em>
                    </blockquote>
                    <hr>
                    <p><strong>Réponse :</strong></p>
                    <p>" . nl2br(htmlspecialchars($reponse)) . "</p>
                ");
            $mailer->send($email);
        } catch (\Exception $e) {
            // ignore
        }

        if ($isAjax) {
            return $this->json([
                'success' => true,
                'message' => 'Réponse envoyée !',
                'messageId' => $newMessage->getId()
            ]);
        }

        $this->addFlash('success', 'Réponse envoyée.');
        return $this->redirectToRoute('mecene_messages');
    }

    #[Route('/{id}/edit', name: 'mecene_message_edit', methods: ['POST'])]
    public function edit(Request $request, ContactMessage $message): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête non autorisée'], 403);
        }

        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est bien l'auteur du message
        if ($message->getEmail() !== $user->getEmail()) {
            return $this->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }

        $newContent = $request->request->get('message');
        if (!$newContent) {
            return $this->json(['success' => false, 'error' => 'Message vide'], 400);
        }

        // Mettre à jour le message
        $originalMessage = $message->getMessage();
        $message->setMessage($newContent);
        
        // Ajouter un marqueur d'édition si ce n'est pas déjà fait
        if (!str_contains($originalMessage, '[MODIFIÉ]')) {
            $message->setMessage('[MODIFIÉ] ' . $newContent);
        }
        
        $this->em->flush();

        return $this->json(['success' => true, 'message' => 'Message modifié']);
    }

    #[Route('/{id}/delete', name: 'mecene_message_delete', methods: ['POST'])]
    public function deleteMessage(Request $request, ContactMessage $message): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête non autorisée'], 403);
        }

        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est bien l'auteur du message
        if ($message->getEmail() !== $user->getEmail()) {
            return $this->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }

        // Remplacer le contenu au lieu de supprimer l'enregistrement
        $message->setMessage('[SUPPRIMÉ] Ce message a été supprimé par son auteur');
        $this->em->flush();

        return $this->json(['success' => true, 'message' => 'Message supprimé']);
    }
}
