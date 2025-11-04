<?php
namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/{id}/repondre', name: 'admin_message_reply', methods: ['POST'])]
    public function reply(
        Request $request,
        ContactMessage $message,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $reponse = trim($request->request->get('reponse'));
        if ($reponse) {
            $message
                ->setReponse($reponse)
                ->setReponduPar($this->getUser())
                ->setReponduLe(new \DateTime())
                ->setLu(true);
            $em->flush();

            // Marquer les notifications comme lues (COMME LES TÂCHES)
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
            $em->flush();

            // Envoi réponse (COMME LES TÂCHES)
            $email = (new Email())
                ->from('contact@groloto.com')
                ->to($message->getEmail())
                ->subject('Re: ' . $this->extractSubject($message->getMessage()))
                ->html("
                    <h3>Bonjour {$message->getNom()},</h3>
                    <p>Merci pour votre message :</p>
                    <blockquote style='border-left:3px solid #ccc; padding-left:10px;'>
                        <em>{$message->getMessage()}</em>
                    </blockquote>
                    <hr>
                    <p><strong>Notre réponse :</strong></p>
                    <p>" . nl2br(htmlspecialchars($reponse)) . "</p>
                    <p>Cordialement,<br>L'équipe Groloto</p>
                ");
            $mailer->send($email);

            $this->addFlash('success', 'Réponse envoyée !');
        }

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