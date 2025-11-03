<?php

namespace App\Controller;

use App\Entity\DemandeAnnulation;
use App\Repository\DemandeAnnulationRepository;
use App\Repository\AffectationTacheRepository;
use App\Repository\BenevoleRepository;
use App\Repository\UtilisateurRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DemandeAnnulationController extends AbstractController
{
    /**
     * Créer une demande d'annulation pour une affectation
     */
    #[Route('/benevole/demander-annulation/{id}', name: 'benevole_demander_annulation', methods: ['POST'])]
    public function demanderAnnulation(
        int $id,
        Request $request,
        AffectationTacheRepository $affectationRepository,
        BenevoleRepository $benevoleRepository,
        DemandeAnnulationRepository $demandeAnnulationRepository,
        UtilisateurRepository $utilisateurRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole.');
            return $this->redirectToRoute('benevoles');
        }

        $affectation = $affectationRepository->find($id);
        if (!$affectation) {
            $this->addFlash('error', 'Affectation non trouvée.');
            return $this->redirectToRoute('benevoles');
        }

        // Vérifier que l'affectation appartient bien au bénévole
        if ($affectation->getBenevole()->getId() !== $benevole->getId()) {
            $this->addFlash('error', 'Cette affectation ne vous appartient pas.');
            return $this->redirectToRoute('benevoles');
        }

        // Vérifier qu'il n'y a pas déjà une demande en attente
        $demandeExistante = $demandeAnnulationRepository->findByAffectationEnAttente($affectation);
        if ($demandeExistante) {
            $this->addFlash('error', 'Vous avez déjà une demande d\'annulation en attente pour cette tâche.');
            return $this->redirectToRoute('benevoles');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('demander_annulation_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevoles');
        }

        // Créer la demande d'annulation
        $demande = new DemandeAnnulation();
        $demande->setAffectation($affectation);
        $demande->setBenevole($benevole);
        $demande->setDateDemande(new \DateTime());
        $demande->setStatut('en_attente');
        $demande->setMotifBenevole($request->request->get('motif', ''));

        $entityManager->persist($demande);
        $entityManager->flush();

        // Notifier tous les admins
        $admins = $utilisateurRepository->findByRole('admin');
        $benevoleNom = $user->getPrenom() . ' ' . $user->getNom();
        $tacheNom = $affectation->getTache()->getTitre();
        
        foreach ($admins as $admin) {
            $notificationService->notifyAdminDemandeAnnulation($admin, $benevoleNom, $tacheNom);
        }

        $this->addFlash('success', 'Votre demande d\'annulation a été envoyée à l\'administrateur.');
        return $this->redirectToRoute('benevoles');
    }

    /**
     * Liste des demandes d'annulation pour l'admin
     */
    #[Route('/admin/demandes-annulations', name: 'admin_demandes_annulations')]
    #[IsGranted('ROLE_ADMIN')]
    public function listeDemandes(DemandeAnnulationRepository $demandeAnnulationRepository): Response
    {
        $demandesEnAttente = $demandeAnnulationRepository->findEnAttente();
        $toutesLesDemandes = $demandeAnnulationRepository->findBy([], ['dateDemande' => 'DESC']);

        return $this->render('demande_annulation/admin_liste.html.twig', [
            'demandes_en_attente' => $demandesEnAttente,
            'toutes_demandes' => $toutesLesDemandes
        ]);
    }

    /**
     * Accepter une demande d'annulation
     */
    #[Route('/admin/accepter-annulation/{id}', name: 'admin_accepter_annulation', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function accepterAnnulation(
        int $id,
        Request $request,
        DemandeAnnulationRepository $demandeAnnulationRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeAnnulationRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('accepter_annulation_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        // Supprimer l'affectation
        $affectation = $demande->getAffectation();
        $tacheNom = $affectation->getTache()->getTitre();
        $entityManager->remove($affectation);

        // Mettre à jour la demande
        $demande->setStatut('acceptee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($request->request->get('message_admin', ''));

        $entityManager->flush();

        // Notifier le bénévole
        $notificationService->notifyBenevoleAnnulationAcceptee(
            $demande->getBenevole()->getUtilisateur(),
            $tacheNom
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande d'annulation de {$benevoleNom} a été acceptée.");
        return $this->redirectToRoute('admin_demandes_annulations');
    }

    /**
     * Refuser une demande d'annulation
     */
    #[Route('/admin/refuser-annulation/{id}', name: 'admin_refuser_annulation', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refuserAnnulation(
        int $id,
        Request $request,
        DemandeAnnulationRepository $demandeAnnulationRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeAnnulationRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('refuser_annulation_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_annulations');
        }

        $messageAdmin = $request->request->get('message_admin', '');
        
        $demande->setStatut('refusee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($messageAdmin);

        $entityManager->flush();

        // Notifier le bénévole
        $notificationService->notifyBenevoleAnnulationRefusee(
            $demande->getBenevole()->getUtilisateur(),
            $demande->getAffectation()->getTache()->getTitre(),
            $messageAdmin
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande d'annulation de {$benevoleNom} a été refusée.");
        return $this->redirectToRoute('admin_demandes_annulations');
    }
}
