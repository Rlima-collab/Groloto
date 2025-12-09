<?php

namespace App\Controller;

use App\Entity\DemandeTache;
use App\Entity\AffectationTache;
use App\Repository\DemandeTacheRepository;
use App\Repository\AffectationTacheRepository;
use App\Repository\BenevoleRepository;
use App\Repository\TacheRepository;
use App\Repository\UtilisateurRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DemandeTacheController extends AbstractController
{
    #[Route('/benevole/demander-tache/{id}', name: 'benevole_demander_tache', methods: ['POST'])]
    public function demanderTache(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        DemandeTacheRepository $demandeTacheRepository,
        UtilisateurRepository $utilisateurRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole.');
            return $this->redirectToRoute('tache_index');
        }

        $tache = $tacheRepository->find($id);
        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        // Vérifier qu'il n'y a pas déjà une demande en attente
        $demandeExistante = $demandeTacheRepository->findByBenevoleAndTache($benevole, $tache);
        if ($demandeExistante && $demandeExistante->getStatut() === 'en_attente') {
            $this->addFlash('error', 'Vous avez déjà une demande en attente pour cette tâche.');
            return $this->redirectToRoute('tache_index');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('demander_tache_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('tache_index');
        }

        // Créer la demande
        $demande = new DemandeTache();
        $demande->setTache($tache);
        $demande->setBenevole($benevole);
        $demande->setDateDemande(new \DateTime());
        $demande->setStatut('en_attente');
        $demande->setMessageBenevole($request->request->get('message', ''));

        $entityManager->persist($demande);
        $entityManager->flush();

        // Notifier tous les admins
        $admins = $utilisateurRepository->findByRole('admin');
        $benevoleNom = $user->getPrenom() . ' ' . $user->getNom();
        $tacheNom = $tache->getTitre();
        
        foreach ($admins as $admin) {
            $notificationService->notifyAdminDemandeTache($admin, $benevoleNom, $tacheNom);
        }

        $this->addFlash('success', 'Votre demande a été envoyée à l\'administrateur.');
        return $this->redirectToRoute('tache_index');
    }

    #[Route('/admin/demandes-taches', name: 'admin_demandes_taches')]
    #[IsGranted('ROLE_ADMIN')]
    public function listeDemandes(DemandeTacheRepository $demandeTacheRepository): Response
    {
        $demandesEnAttente = $demandeTacheRepository->findBy(['statut' => 'en_attente'], ['date_demande' => 'ASC']);
        $toutesLesDemandes = $demandeTacheRepository->findBy([], ['date_demande' => 'DESC']);

        return $this->render('demande_tache/admin_liste.html.twig', [
            'demandes_en_attente' => $demandesEnAttente,
            'toutes_demandes' => $toutesLesDemandes
        ]);
    }

    #[Route('/admin/accepter-demande-tache/{id}', name: 'admin_accepter_demande_tache', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function accepterDemande(
        int $id,
        Request $request,
        DemandeTacheRepository $demandeTacheRepository,
        AffectationTacheRepository $affectationRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeTacheRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('accepter_demande_tache_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        // Créer l'affectation
        $affectation = new AffectationTache();
        $affectation->setTache($demande->getTache());
        $affectation->setBenevole($demande->getBenevole());
        $affectation->setUtilisateur($demande->getBenevole()->getUtilisateur());
        $affectation->setDateAffectation(new \DateTime());
        $affectation->setStatut('assigne');
        
        $entityManager->persist($affectation);

        // Mettre à jour la demande
        $demande->setStatut('acceptee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($request->request->get('message_admin', ''));

        $entityManager->flush();

        // Notifier le bénévole
        $notificationService->notifyBenevoleDemandeAcceptee(
            $demande->getBenevole()->getUtilisateur(),
            $demande->getTache()->getTitre()
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande de {$benevoleNom} a été acceptée.");
        return $this->redirectToRoute('admin_demandes_taches');
    }

    #[Route('/admin/refuser-demande-tache/{id}', name: 'admin_refuser_demande_tache', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refuserDemande(
        int $id,
        Request $request,
        DemandeTacheRepository $demandeTacheRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeTacheRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('refuser_demande_tache_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        $messageAdmin = $request->request->get('message_admin', '');
        
        $demande->setStatut('refusee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($messageAdmin);

        $entityManager->flush();

        // Notifier le bénévole
        $notificationService->notifyBenevoleDemandeRefusee(
            $demande->getBenevole()->getUtilisateur(),
            $demande->getTache()->getTitre(),
            $messageAdmin
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande de {$benevoleNom} a été refusée.");
        return $this->redirectToRoute('admin_demandes_taches');
    }
}
