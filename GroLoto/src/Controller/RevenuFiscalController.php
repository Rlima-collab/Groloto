<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Entity\Notification;
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

    #[Route('/mecene/recu/send', name: 'mecene_recu_send', methods: ['POST'])]
    #[IsGranted('ROLE_MECENE')]
    public function sendFromMecene(Request $request, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('send_recu_mecene', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('mecenes');
        }

        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        $nom = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? '')) ?: $user->getEmail();

        // handle uploaded file
        /** @var UploadedFile|null $file */
        $file = $request->files->get('recu_file');
        $publicPath = '';
        if ($file instanceof UploadedFile) {
            $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/revenus_fiscaux';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
            $ext = $file->guessExtension() ?: 'pdf';
            $filename = uniqid('recu_') . '.' . $ext;
            try {
                $file->move($targetDir, $filename);
                $publicPath = '/uploads/revenus_fiscaux/' . $filename;
            } catch (\Throwable $e) {
                // ignore move errors
            }
        }

        $messageText = 'Reçu fiscal (mécène) envoyé par ' . $nom;
        if ($publicPath !== '') {
            $messageText .= ' Fichier: ' . $publicPath;
        }

        $cm = new ContactMessage();
        $cm->setNom($nom)
            ->setEmail($user->getEmail())
            ->setDestinataire('REVENU_FISCAL')
            ->setMessage($messageText);

        $this->em->persist($cm);
        $this->em->flush();

        // notifier tous les admins
        $admins = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom IN (:roles)')
            ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
            ->getQuery()
            ->getResult();

        $adminEmails = [];
        foreach ($admins as $adm) {
            if ($adm->getEmail()) $adminEmails[] = $adm->getEmail();
            try {
                $notification = new Notification();
                $notification->setDestinataire($adm)
                    ->setType('revenu_fiscal')
                    ->setMessage("Reçu fiscal envoyé par $nom")
                    ->setLien('/admin/revenus-fiscaux')
                    ->setLue(false);
                $this->em->persist($notification);
            } catch (\Throwable $e) {
                // ignore notification errors
            }
        }
        $this->em->flush();

        if (!empty($adminEmails)) {
            try {
                $emailObj = (new Email())
                    ->from($user->getEmail())
                    ->to(...$adminEmails)
                    ->subject('Reçu fiscal (mécène)')
                    ->html(sprintf('Le mécène %s a envoyé son reçu fiscal. %s', htmlspecialchars($nom), $publicPath ? 'Téléchargement: <a href="' . $publicPath . '">' . $publicPath . '</a>' : ''));
                $mailer->send($emailObj);
            } catch (\Throwable $e) {
                // ignore mail errors
            }
        }

        $this->addFlash('success', 'Le reçu fiscal a été envoyé aux administrateurs.');
        return $this->redirectToRoute('mecene_revenu_fiscal_index');
    }

    #[Route('/benevole/recu/send', name: 'benevole_recu_send', methods: ['POST'])]
    #[IsGranted('ROLE_BENEVOLE')]
    public function sendFromBenevole(Request $request, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('send_recu_benevole', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('benevole_revenu_fiscal_index');
        }

        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        $nom = trim(($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? '')) ?: $user->getEmail();

        // handle uploaded file
        /** @var UploadedFile|null $file */
        $file = $request->files->get('recu_file');
        $publicPath = '';
        if ($file instanceof UploadedFile) {
            $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/revenus_fiscaux';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
            $ext = $file->guessExtension() ?: 'pdf';
            $filename = uniqid('recu_') . '.' . $ext;
            try {
                $file->move($targetDir, $filename);
                $publicPath = '/uploads/revenus_fiscaux/' . $filename;
            } catch (\Throwable $e) {
                // ignore move errors
            }
        }

        $messageText = 'Reçu fiscal (bénévole) envoyé par ' . $nom;
        if ($publicPath !== '') {
            $messageText .= ' Fichier: ' . $publicPath;
        }

        $cm = new ContactMessage();
        $cm->setNom($nom)
            ->setEmail($user->getEmail())
            ->setDestinataire('REVENU_FISCAL')
            ->setMessage($messageText);

        $this->em->persist($cm);
        $this->em->flush();

        // notifier admins
        $admins = $this->em->getRepository(Utilisateur::class)
            ->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.nom IN (:roles)')
            ->setParameter('roles', ['ROLE_ADMIN', 'admin'])
            ->getQuery()
            ->getResult();

        $adminEmails = [];
        foreach ($admins as $adm) {
            if ($adm->getEmail()) $adminEmails[] = $adm->getEmail();
            try {
                $notification = new Notification();
                $notification->setDestinataire($adm)
                    ->setType('revenu_fiscal')
                    ->setMessage("Reçu fiscal envoyé par $nom")
                    ->setLien('/admin/revenus-fiscaux')
                    ->setLue(false);
                $this->em->persist($notification);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        $this->em->flush();

        if (!empty($adminEmails)) {
            try {
                $emailObj = (new Email())
                    ->from($user->getEmail())
                    ->to(...$adminEmails)
                    ->subject('Reçu fiscal (bénévole)')
                    ->html(sprintf('Le bénévole %s a envoyé son reçu fiscal. %s', htmlspecialchars($nom), $publicPath ? 'Téléchargement: <a href="' . $publicPath . '">' . $publicPath . '</a>' : ''));
                $mailer->send($emailObj);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $this->addFlash('success', 'Le reçu fiscal a été envoyé aux administrateurs.');
        return $this->redirectToRoute('benevole_revenu_fiscal_index');
    }

    #[Route('/admin/revenus-fiscaux', name: 'admin_revenu_fiscal_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        $repo = $this->em->getRepository(ContactMessage::class);
        $qb = $repo->createQueryBuilder('cm')
            ->where('cm.destinataire = :dest')
            ->setParameter('dest', 'REVENU_FISCAL')
            ->orderBy('cm.createdAt', 'DESC');

        if ($q !== '') {
            $qb->andWhere('cm.nom LIKE :q OR cm.email LIKE :q OR cm.message LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $messages = $qb->getQuery()->getResult();

        return $this->render('admin/revenu_fiscal/index.html.twig', ['messages' => $messages, 'q' => $q]);
    }

    #[Route('/mecene/revenus-fiscaux', name: 'mecene_revenu_fiscal_index')]
    #[IsGranted('ROLE_MECENE')]
    public function meceneIndex(): Response
    {
        $user = $this->getUser();
        $messages = $this->em->getRepository(ContactMessage::class)->findBy(['email' => $user->getEmail(), 'destinataire' => 'REVENU_FISCAL'], ['createdAt' => 'DESC']);
        return $this->render('mecene/revenu_fiscal/index.html.twig', ['messages' => $messages]);
    }

    #[Route('/benevole/revenus-fiscaux', name: 'benevole_revenu_fiscal_index')]
    #[IsGranted('ROLE_BENEVOLE')]
    public function benevoleIndex(): Response
    {
        $user = $this->getUser();
        $messages = $this->em->getRepository(ContactMessage::class)->findBy(['email' => $user->getEmail(), 'destinataire' => 'REVENU_FISCAL'], ['createdAt' => 'DESC']);
        return $this->render('benevole/revenu_fiscal/index.html.twig', ['messages' => $messages]);
    }
}
