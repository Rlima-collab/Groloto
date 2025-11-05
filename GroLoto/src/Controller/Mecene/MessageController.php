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
#[IsGranted('ROLE_BENEVOLE')]
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
            $messages = $this->em->getRepository(ContactMessage::class)
                ->createQueryBuilder('cm')
                ->where('cm.parent_id IS NULL')
                ->andWhere('cm.destinataire = :email OR cm.email = :email')
                ->setParameter('email', $email)
                ->orderBy('cm.createdAt', 'DESC')
                ->getQuery()
                ->getResult();

            // Pour chaque message racine, charger la conversation complète
            foreach ($messages as $msg) {
                $msg->conversation = $this->getConversationThread($msg->getId());
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

    // AJAX compose -> envoie au contact@groloto.com et stocke le message
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

        // Formater le message avec le sujet
        $fullMessage = $subject ? "[{$subject}] {$messageText}" : $messageText;

        $cm = new ContactMessage();
        $cm->setNom($nom)
            ->setEmail($email)
            ->setDestinataire($adminEmail)
            ->setMessage($fullMessage);

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
                    ->setLien('/admin/messages/' . $cm->getId())
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

        return $this->json(['success' => true, 'message' => 'Message envoyé !']);
    }

    #[Route('/{id}/delete', name: 'mecene_message_delete', methods: ['POST'])]
    public function delete(ContactMessage $message): Response
    {
        // sécurité : n'autoriser que le propriétaire (email)
        $user = $this->getUser();
        if ($user->getEmail() !== $message->getEmail()) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('mecene_messages');
        }

        $this->em->remove($message);
        $this->em->flush();
        $this->addFlash('success', 'Message supprimé.');

        return $this->redirectToRoute('mecene_messages');
    }

    #[Route('/{id}/repondre', name: 'mecene_message_reply', methods: ['POST'])]
    public function reply(Request $request, ContactMessage $message, MailerInterface $mailer): Response
    {
        $reponse = trim($request->request->get('reponse')) ?: trim($request->request->get('message'));
        if (!$reponse) {
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

        // Créer un nouveau message de réponse lié au parent
        $fullMessage = "[{$subject}] " . $reponse;
        
        // Déterminer le message racine (parent_id)
        $rootParentId = $message->getParentId() ?? $message->getId();
        
        $newMessage = new ContactMessage();
        $newMessage->setNom($nom)
            ->setEmail($user->getEmail())
            ->setDestinataire($adminEmail)
            ->setMessage($fullMessage)
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
                    ->setLien('/admin/messages')
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

        $this->addFlash('success', 'Réponse envoyée.');
        return $this->redirectToRoute('mecene_messages');
    }
}
