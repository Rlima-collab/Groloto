<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\RecuFiscal;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/')]
class RevenuFiscalController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/admin/recus-fiscaux/envoyer', name: 'admin_recu_fiscal_send', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function sendRecuFiscal(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $destinataireId = $request->request->get('destinataire');
            $type = $request->request->get('type');
            $annee = (int) $request->request->get('annee');

            if (!$this->isCsrfTokenValid('send_recu_fiscal', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token invalide.');
                return $this->redirectToRoute('admin_recu_fiscal_send');
            }

            $destinataire = $this->em->getRepository(Utilisateur::class)->find($destinataireId);
            if (!$destinataire) {
                $this->addFlash('error', 'Destinataire introuvable.');
                return $this->redirectToRoute('admin_recu_fiscal_send');
            }

            // Vérifier que le type correspond au rôle de l'utilisateur (validation légère)
            $roleNom = strtoupper($destinataire->getRole()->getNom());
            $isValidRole = str_contains($roleNom, 'MECENE') || str_contains($roleNom, 'BENEVOLE') ||
                           str_contains($roleNom, 'ROLE_MECENE') || str_contains($roleNom, 'ROLE_BENEVOLE');

            if (!$isValidRole) {
                $this->addFlash('error', 'L\'utilisateur n\'a pas un rôle valide pour recevoir un reçu fiscal.');
                return $this->redirectToRoute('admin_recu_fiscal_send');
            }

            // Handle uploaded file
            /** @var UploadedFile|null $file */
            $file = $request->files->get('recu_file');
            if (!$file instanceof UploadedFile) {
                $this->addFlash('error', 'Aucun fichier sélectionné.');
                return $this->redirectToRoute('admin_recu_fiscal_send');
            }

            $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/recus_fiscaux';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $ext = $file->guessExtension() ?: 'pdf';
            $filename = uniqid('recu_fiscal_') . '.' . $ext;

            try {
                $file->move($targetDir, $filename);
                $publicPath = '/uploads/recus_fiscaux/' . $filename;
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                return $this->redirectToRoute('admin_recu_fiscal_send');
            }

            // Créer le reçu fiscal
            $recuFiscal = new RecuFiscal();
            $recuFiscal->setDestinataire($destinataire)
                ->setType($type)
                ->setFichier($publicPath)
                ->setAnnee($annee)
                ->setEnvoyePar($this->getUser());

            $this->em->persist($recuFiscal);

            // Créer une notification
            $notification = new Notification();
            $notification->setDestinataire($destinataire)
                ->setType('recu_fiscal')
                ->setMessage("Votre reçu fiscal $type pour l'année $annee est disponible")
                ->setLien('/' . $type . '/recus-fiscaux')
                ->setLue(false);

            $this->em->persist($notification);
            $this->em->flush();

            // Envoyer un email
            try {
                $emailObj = (new Email())
                    ->from('noreply@groloto.fr')
                    ->to($destinataire->getEmail())
                    ->subject("Reçu fiscal $type - Année $annee")
                    ->html(sprintf(
                        'Bonjour,<br><br>Votre reçu fiscal %s pour l\'année %d est maintenant disponible.<br><br>Vous pouvez le télécharger depuis votre espace personnel.<br><br>Cordialement,<br>L\'équipe GroLoto',
                        $type === 'mecene' ? 'mécène' : 'bénévole',
                        $annee
                    ));
                $mailer->send($emailObj);
            } catch (\Throwable $e) {
                // Ignore mail errors
            }

            $this->addFlash('success', 'Le reçu fiscal a été envoyé avec succès.');
            return $this->redirectToRoute('admin_recu_fiscal_send');
        }

        // GET request - afficher le formulaire
        $mecs = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom LIKE :role')
            ->setParameter('role', '%MECENE%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $bens = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom LIKE :role')
            ->setParameter('role', '%BENEVOLE%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/recu_fiscal/send.html.twig', [
            'mecenes' => $mecs,
            'benevoles' => $bens,
            'current_year' => date('Y')
        ]);
    }

    #[Route('/mecene/recus-fiscaux', name: 'mecene_recu_fiscal_index')]
    #[IsGranted('ROLE_MECENE')]
    public function meceneIndex(): Response
    {
        $user = $this->getUser();
        $recus = $this->em->getRepository(RecuFiscal::class)
            ->findBy(['destinataire' => $user, 'type' => 'mecene'], ['createdAt' => 'DESC']);

        return $this->render('mecene/revenu_fiscal/index.html.twig', ['recus' => $recus]);
    }

    #[Route('/benevole/recus-fiscaux', name: 'benevole_recu_fiscal_index')]
    #[IsGranted('ROLE_BENEVOLE')]
    public function benevoleIndex(): Response
    {
        $user = $this->getUser();
        $recus = $this->em->getRepository(RecuFiscal::class)
            ->findBy(['destinataire' => $user, 'type' => 'benevole'], ['createdAt' => 'DESC']);

        return $this->render('benevole/revenu_fiscal/index.html.twig', ['recus' => $recus]);
    }

    #[Route('/benevole/demande-recu-fiscal', name: 'benevole_recu_send', methods: ['POST'])]
    #[IsGranted('ROLE_BENEVOLE')]
    public function benevoleDemandeRecu(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('send_recu_benevole', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevole_mon_planning');
        }

        $user = $this->getUser();

        // Envoyer une notification aux admins
        $admins = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom LIKE :role')
            ->setParameter('role', '%ADMIN%')
            ->getQuery()
            ->getResult();

        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setDestinataire($admin)
                ->setType('demande_recu')
                ->setMessage(sprintf('%s %s demande son reçu fiscal bénévole', $user->getPrenom(), $user->getNom()))
                ->setLien('/admin/recus-fiscaux/envoyer')
                ->setLue(false);

            $this->em->persist($notification);
        }

        $this->em->flush();

        $this->addFlash('success', 'Votre demande de reçu fiscal a été envoyée aux administrateurs.');
        return $this->redirectToRoute('benevole_mon_planning');
    }

    #[Route('/mecene/demande-recu-fiscal', name: 'mecene_recu_send', methods: ['POST'])]
    #[IsGranted('ROLE_MECENE')]
    public function meceneDemandeRecu(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('send_recu_mecene', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('mecene_dashboard');
        }

        $user = $this->getUser();

        // Envoyer une notification aux admins
        $admins = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom LIKE :role')
            ->setParameter('role', '%ADMIN%')
            ->getQuery()
            ->getResult();

        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setDestinataire($admin)
                ->setType('demande_recu')
                ->setMessage(sprintf('%s %s demande son reçu fiscal mécène', $user->getPrenom(), $user->getNom()))
                ->setLien('/admin/recus-fiscaux/envoyer')
                ->setLue(false);

            $this->em->persist($notification);
        }

        $this->em->flush();

        $this->addFlash('success', 'Votre demande de reçu fiscal a été envoyée aux administrateurs.');
        return $this->redirectToRoute('mecene_dashboard');
    }
}
