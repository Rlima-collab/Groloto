<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/admin/messages')]
#[IsGranted('ROLE_ADMIN')]
class ContactMessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'admin_messages')]
    public function index(): Response
    {
        $messages = $this->em->getRepository(ContactMessage::class)
            ->findAllOrdered();

        return $this->render('admin/message/messages.html.twig', [
            'messages' => $messages,
        ]);
    }

    // POPUP NOUVEAU MESSAGE (AJAX ONLY)
    #[Route('/nouveau', name: 'admin_message_new', methods: ['POST'])]
    public function new(Request $request, MailerInterface $mailer): JsonResponse
    {
        // Sécurité : AJAX uniquement
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête non autorisée'], 403);
        }

        $to = trim($request->request->get('to'));
        $subject = trim($request->request->get('subject'));
        $message = trim($request->request->get('message'));

        if (!$to || !$message) {
            return $this->json(['success' => false, 'error' => 'Destinataire et message requis'], 400);
        }

        try {
            $email = (new Email())
                ->from('contact@groloto.com')
                ->to($to)
                ->subject($subject ?: 'Message depuis Groloto')
                ->html(nl2br(htmlspecialchars($message)));

            $mailer->send($email);

            return $this->json(['success' => true, 'message' => 'Message envoyé !']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Échec d\'envoi'], 500);
        }
    }

    #[Route('/{id}/repondre', name: 'admin_message_reply', methods: ['POST'])]
    public function reply(
        Request $request,
        ContactMessage $message,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $reponse = trim($request->request->get('reponse'));
        if (!$reponse) {
            $this->addFlash('error', 'La réponse ne peut pas être vide.');
            return $this->redirectToRoute('admin_messages');
        }

        $message
            ->setReponse($reponse)
            ->setReponduPar($this->getUser())
            ->setReponduLe(new \DateTime())
            ->setLu(true);
        $em->flush();

        // 1. Marquer les notifications existantes comme lues
        $notifications = $em->getRepository(Notification::class)
            ->createQueryBuilder('n')
            ->where('n.destinataire = :user')
            ->andWhere('n.type = :type')
            ->andWhere('n.lue = false')
            ->setParameter('user', $this->getUser())
            ->setParameter('type', 'contact')
            ->getQuery()
            ->getResult();

        foreach ($notifications as $notif) {
            $notif->setLue(true);
        }

        // 2. Créer une notification pour l'expéditeur du message
        $expediteur = $em->getRepository(\App\Entity\Utilisateur::class)
            ->findOneByEmail($message->getEmail());

        if ($expediteur) {
            $notification = new Notification();
            $notification
                ->setDestinataire($expediteur)
                ->setType('reponse_contact')
                ->setMessage("Un admin a répondu à votre message")
                ->setLien('/contact/message/' . $message->getId())  // L'utilisateur pourra ouvrir la réponse spécifique
                ->setLue(false)
                ->setCreatedAt(new \DateTime());

            $em->persist($notification);
        }

        $em->flush();

        // 3. Envoi de la réponse par email
        $email = (new Email())
            ->from('contact@groloto.com')
            ->to($message->getEmail())
            ->subject('Re: ' . $this->extractSubject($message->getMessage()))
            ->html("
                <h3>Bonjour {$message->getNom()},</h3>
                <p>Merci pour votre message :</p>
                <blockquote style='border-left:3px solid #ccc; padding-left:10px; margin: 10px 0;'>
                    <em>{$message->getMessage()}</em>
                </blockquote>
                <hr>
                <p><strong>Notre réponse :</strong></p>
                <p>" . nl2br(htmlspecialchars($reponse)) . "</p>
                <p>Vous pouvez répondre à ce message via le formulaire de contact sur notre site.</p>
                <p>Cordialement,<br>L'équipe Groloto</p>
            ");

        $mailer->send($email);
        $this->addFlash('success', 'Réponse envoyée !');

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/delete', name: 'admin_message_delete', methods: ['POST'])]
    public function delete(ContactMessage $message, EntityManagerInterface $em): Response
    {
        $em->remove($message);
        $em->flush();
        $this->addFlash('success', 'Message supprimé.');

        return $this->redirectToRoute('admin_messages');
    }

    private function extractSubject(string $message): string
    {
        if (preg_match('/^\[([^\]]+)\]/', $message, $matches)) {
            return $matches[1];
        }
        return 'Votre message';
    }
}