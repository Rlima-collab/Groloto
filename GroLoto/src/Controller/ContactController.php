<?php
// src/Controller/ContactController.php
namespace App\Controller;

use App\Dto\ContactDto;
use App\Form\ContactForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ContactController extends AbstractController
{
    public function __construct(
        private string $appContactEmail
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $contactDto = new ContactDto();

        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            $contactDto->setNom($user->getNom() ?? $user->getUsername()); 
            $contactDto->setEmail($user->getEmail());
        }

        $form = $this->createForm(ContactForm::class, $contactDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = (new Email())
                    ->from('contact@groloto.com')
                    ->replyTo($contactDto->getEmail())
                    ->to($this->appContactEmail)
                    ->subject($contactDto->getSujet() ?? 'Message de contact - Groloto')
                    ->html($this->renderEmailTemplate($contactDto));

                $mailer->send($email);

                $this->addFlash('success', 'Votre message a été envoyé avec succès !');
                return $this->redirectToRoute('app_contact');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.');
                if ($this->getParameter('kernel.environment') === 'dev') {
                    $this->addFlash('debug', 'Erreur: ' . $e->getMessage());
                }
            }
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function renderEmailTemplate(ContactDto $contact): string
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #f8f9fa; padding: 15px; border-radius: 5px; }
                    .content { margin: 20px 0; }
                    .field { margin-bottom: 10px; }
                    .label { font-weight: bold; color: #333; }
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
                            <span class='label'>Email :</span> {$contact->getEmail()}
                        </div>
                        <div class='field'>
                            <span class='label'>Sujet :</span> " . ($contact->getSujet() ?? 'Non spécifié') . "
                        </div>
                        <div class='field'>
                            <span class='label'>Message :</span>
                            <div style='margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;'>
                                " . nl2br(htmlspecialchars($contact->getMessage())) . "
                            </div>
                        </div>
                        <div class='field'>
                            <span class='label'>Date :</span> " . (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('d/m/Y H:i') . "
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}