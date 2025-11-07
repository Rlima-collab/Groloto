<?php
namespace App\Controller;

use App\Dto\ContactDto;
use App\Entity\ContactMessage;
use App\Entity\Notification;
use App\Form\ContactForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ContactController extends AbstractController
{
    public function __construct(
        private string $appContactEmail,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/contact/message/{id}/reply', name: 'app_contact_message_reply_user', methods: ['POST'])]
    public function replyToMessage(ContactMessage $message, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $user->getEmail() !== $message->getEmail()) {
            return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
        }

        $content = trim($request->request->get('message') ?? $request->request->get('reponse') ?? '');
        if (!$content) {
            return $this->json(['success' => false, 'error' => 'Message vide'], 400);
        }

        // Create a new ContactMessage representing the user's reply
        $reply = new ContactMessage();
        $reply->setNom($user->getNom() ?? $user->getUserIdentifier())
            ->setEmail($user->getEmail())
            ->setMessage($content)
            ->setCreatedAt(new \DateTime());

        $em->persist($reply);

        // Notify admins about the reply
        $admins = $em->getRepository(\App\Entity\Utilisateur::class)->findByRoleName('admin');
        foreach ($admins as $admin) {
            $notif = new Notification();
            $notif->setDestinataire($admin)
                ->setType('contact')
                ->setMessage($user->getNom() . " a répondu à votre message")
                ->setLien('/admin/messages')
                ->setLue(false)
                ->setCreatedAt(new \DateTime());
            $em->persist($notif);
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/contact/message/{id}', name: 'app_contact_message_view', methods: ['GET'])]
    public function viewMessage(ContactMessage $message): JsonResponse
    {
        $user = $this->getUser();

        // Security: allow admin or the message owner (by email)
        if ($this->isGranted('ROLE_ADMIN') || ($user && $user->getEmail() === $message->getEmail())) {
            return $this->json([
                'id' => $message->getId(),
                'nom' => $message->getNom(),
                'email' => $message->getEmail(),
                'message' => $message->getMessage(),
                'createdAt' => $message->getCreatedAt()->format('d/m/Y H:i'),
                'reponse' => $message->getReponse(),
                'reponduLe' => $message->getReponduLe() ? $message->getReponduLe()->format('d/m/Y H:i') : null,
                'reponduPar' => $message->getReponduPar() ? $message->getReponduPar()->getNom() : null,
            ]);
        }

        return $this->json(['error' => 'Accès refusé'], 403);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $contactDto = new ContactDto();
        $user = $this->getUser();

        if ($user instanceof UserInterface) {
            $contactDto->setNom($user->getNom() ?? $user->getPrenom() ?? $user->getUserIdentifier());
            $contactDto->setEmail($user->getEmail());
        }

        $form = $this->createForm(ContactForm::class, $contactDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si c'est une requête AJAX (réponse depuis la modal)
            $isAjax = $request->isXmlHttpRequest();

            try {
                // 1. Envoi de l'email
                $email = (new Email())
                    ->from('contact@groloto.com')
                    ->replyTo($contactDto->getEmail())
                    ->to($this->appContactEmail)
                    ->subject($contactDto->getSujet() ?? 'Message de contact - Groloto')
                    ->html($this->renderEmailTemplate($contactDto));
                $mailer->send($email);

                // 2. Enregistrement du message en base
                $contactMessage = new ContactMessage();
                $contactMessage
                    ->setNom($contactDto->getNom())
                    ->setEmail($contactDto->getEmail())
                    ->setMessage(($contactDto->getSujet() ? "[{$contactDto->getSujet()}] " : "") . $contactDto->getMessage())
                    ->setCreatedAt(new \DateTime());
                $this->em->persist($contactMessage);

                // 3. Déterminer s'il s'agit d'une nouvelle conversation ou d'une réponse
                $isReponse = $this->em->getRepository(ContactMessage::class)
                    ->count(['email' => $contactDto->getEmail()]) > 0;

                // 4. Création de la notification pour l'admin
                $users = $this->em->getRepository(\App\Entity\Utilisateur::class)->findByRoleName('admin');
                if (!empty($users)) {
                    // Envoi de la notification à tous les admins
                    foreach ($users as $admin) {
                        $notification = new Notification();
                        $notification
                            ->setDestinataire($admin)
                            ->setType('contact')
                            ->setMessage($isReponse 
                                ? "{$contactDto->getNom()} a répondu à votre message" 
                                : "Nouveau message de {$contactDto->getNom()}")
                            ->setLien('/admin/messages')
                            ->setLue(false)
                            ->setCreatedAt(new \DateTime());

                        $this->em->persist($notification);
                    }
                }

                // Sauvegarde tout (COMME LES DEMANDES DE TÂCHE)
                $this->em->flush();

                if ($isAjax) {
                    return $this->json(['success' => true]);
                } else {
                    $this->addFlash('success', 'Votre message a été envoyé avec succès !');
                    return $this->redirectToRoute('app_contact');
                }
            } catch (\Exception $e) {
                if ($isAjax) {
                    return $this->json([
                        'success' => false,
                        'error' => $this->getParameter('kernel.environment') === 'dev' ? $e->getMessage() : 'Une erreur est survenue'
                    ], 500);
                } else {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
                    if ($this->getParameter('kernel.environment') === 'dev') {
                        $this->addFlash('debug', 'Erreur: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function renderEmailTemplate(ContactDto $contact): string
    {
        $date = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('d/m/Y H:i');

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: #006909; color: white; padding: 20px; text-align: center; }
                    .header h2 { margin: 0; font-size: 20px; }
                    .content { padding: 25px; }
                    .field { margin-bottom: 15px; }
                    .label { font-weight: bold; color: #333; display: inline-block; width: 100px; }
                    .message-box { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-top: 8px; font-size: 14px; line-height: 1.6; }
                    .footer { text-align: center; padding: 15px; font-size: 12px; color: #777; background: #f1f1f1; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Nouveau message de contact - Groloto</h2>
                    </div>
                    <div class='content'>
                        <div class='field'>
                            <span class='label'>Nom :</span> {$contact->getNom()}
                        </div>
                        <div class='field'>
                            <span class='label'>Email :</span> <a href='mailto:{$contact->getEmail()}'>{$contact->getEmail()}</a>
                        </div>
                        <div class='field'>
                            <span class='label'>Sujet :</span> " . ($contact->getSujet() ?? 'Non spécifié') . "
                        </div>
                        <div class='field'>
                            <span class='label'>Message :</span>
                            <div class='message-box'>
                                " . nl2br(htmlspecialchars($contact->getMessage())) . "
                            </div>
                        </div>
                        <div class='field'>
                            <span class='label'>Envoyé le :</span> {$date}
                        </div>
                    </div>
                    <div class='footer'>
                        Groloto Manager © " . date('Y') . " • Tous droits réservés
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}