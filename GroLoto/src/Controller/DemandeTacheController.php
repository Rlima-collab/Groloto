<?php

namespace App\Controller;

use App\Entity\DemandeTache;
use App\Entity\AffectationTache;
use App\Entity\Notification;
use App\Repository\DemandeTacheRepository;
use App\Repository\TacheRepository;
use App\Repository\BenevoleRepository;
use App\Repository\AffectationTacheRepository;
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
    /**
     * Liste des tâches disponibles pour qu'un bénévole puisse faire une demande
     */
    #[Route('/benevole/taches-disponibles', name: 'benevole_taches_disponibles')]
    public function tachesDisponibles(
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        AffectationTacheRepository $affectationRepository,
        DemandeTacheRepository $demandeRepository
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole pour accéder à cette page.');
            return $this->redirectToRoute('benevoles');
        }

        // Récupérer toutes les tâches futures
        $toutesToutesTaches = $tacheRepository->findBy([], ['debut' => 'ASC']);
        $tachesDisponibles = [];

        foreach ($toutesToutesTaches as $tache) {
            // Vérifier si la tâche est future
            if ($tache->getDebut() < new \DateTime()) {
                continue;
            }

            // Vérifier si le bénévole est déjà affecté
            $dejaAffecte = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
            if ($dejaAffecte) {
                continue;
            }

            // Vérifier si le bénévole a déjà fait une demande en attente
            $demandeEnAttente = $demandeRepository->findByBenevoleAndTache($benevole, $tache);
            if ($demandeEnAttente) {
                continue;
            }

            // Vérifier si la tâche n'est pas complète
            $nbAffectations = $affectationRepository->countBenevolesByTache($tache);
            if ($nbAffectations < $tache->getMaxPersonnes()) {
                $tachesDisponibles[] = [
                    'tache' => $tache,
                    'nbAffectations' => $nbAffectations
                ];
            }
        }

        // Récupérer les demandes du bénévole
        $mesDemandes = $demandeRepository->findByBenevole($benevole);

        return $this->render('demande_tache/index.html.twig', [
            'taches_disponibles' => $tachesDisponibles,
            'mes_demandes' => $mesDemandes,
            'benevole' => $benevole
        ]);
    }

    /**
     * Créer une demande pour rejoindre une tâche
     */
    #[Route('/benevole/demander-tache/{id}', name: 'benevole_demander_tache', methods: ['POST'])]
    public function demanderTache(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        DemandeTacheRepository $demandeRepository,
        AffectationTacheRepository $affectationRepository,
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

        $tache = $tacheRepository->find($id);
        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        // Vérifications
        $dejaAffecte = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
        if ($dejaAffecte) {
            $this->addFlash('error', 'Vous êtes déjà affecté à cette tâche.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        $demandeExistante = $demandeRepository->findByBenevoleAndTache($benevole, $tache);
        if ($demandeExistante) {
            $this->addFlash('error', 'Vous avez déjà une demande en attente pour cette tâche.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        $nbAffectations = $affectationRepository->countBenevolesByTache($tache);
        if ($nbAffectations >= $tache->getMaxPersonnes()) {
            $this->addFlash('error', 'Cette tâche est complète.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('demander_tache_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevole_taches_disponibles');
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

        $admins = $utilisateurRepository->findByRoleName('admin');
        $benevoleNom = trim((($benevole->getUtilisateur()->getPrenom() ?? '') . ' ' . ($benevole->getUtilisateur()->getNom() ?? '')));
        $tacheNom = $tache->getTitre() ?? 'Tâche #' . $tache->getId();

        foreach ($admins as $admin) {
            $notificationService->notifyAdminDemandeTache($admin, $benevoleNom, $tacheNom);
        }

        $this->addFlash('success', 'Votre demande a été envoyée à l\'administrateur.');
        return $this->redirectToRoute('benevole_taches_disponibles');
    }

    /**
     * Annuler une demande
     */
    #[Route('/benevole/annuler-demande/{id}', name: 'benevole_annuler_demande', methods: ['POST'])]
    public function annulerDemande(
        int $id,
        Request $request,
        DemandeTacheRepository $demandeRepository,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        $demande = $demandeRepository->find($id);
        if (!$demande || $demande->getBenevole()->getId() !== $benevole->getId()) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette demande.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('annuler_demande_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevole_taches_disponibles');
        }

        $entityManager->remove($demande);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande a été annulée.');
        return $this->redirectToRoute('benevole_taches_disponibles');
    }

    /**
     * Liste des demandes pour l'admin
     */
    #[Route('/admin/demandes-taches', name: 'admin_demandes_taches')]
    #[IsGranted('ROLE_ADMIN')]

    public function listeDemandes(
        DemandeTacheRepository $demandeRepository,
        AffectationTacheRepository $affectationRepository
    ): Response

    {
        $demandesEnAttente = $demandeRepository->findEnAttente();
        $toutesLesDemandes = $demandeRepository->findBy([], ['date_demande' => 'DESC']);


        // Calculer le nombre d'affectations pour chaque tâche
        $affectationsParTache = [];
        foreach (array_merge($demandesEnAttente, $toutesLesDemandes) as $demande) {
            $tacheId = $demande->getTache()->getId();
            if (!isset($affectationsParTache[$tacheId])) {
                $affectationsParTache[$tacheId] = $affectationRepository->countBenevolesByTache($demande->getTache());

            }
        }

        return $this->render('demande_tache/admin_liste.html.twig', [
            'demandes_en_attente' => $demandesEnAttente,
            'toutes_demandes' => $toutesLesDemandes,

            'affectations_par_tache' => $affectationsParTache

        ]);
    }

    /**
     * Accepter une demande
     */
    #[Route('/admin/accepter-demande/{id}', name: 'admin_accepter_demande', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function accepterDemande(
        int $id,
        Request $request,
        DemandeTacheRepository $demandeRepository,
        AffectationTacheRepository $affectationRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeRepository->find($id);
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
        if (!$this->isCsrfTokenValid('accepter_demande_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        // Vérifier qu'il y a encore de la place
        $tache = $demande->getTache();
        $nbAffectations = $affectationRepository->countBenevolesByTache($tache);
        if ($nbAffectations >= $tache->getMaxPersonnes()) {
            $this->addFlash('error', 'Cette tâche est maintenant complète.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        // Créer l'affectation
        $affectation = new AffectationTache();
        $affectation->setTache($tache);
        $affectation->setBenevole($demande->getBenevole());
        $affectation->setUtilisateur($demande->getBenevole()->getUtilisateur());
        $affectation->setDateAffectation(new \DateTime());
        $affectation->setStatut('assigne');

        // Mettre à jour la demande
        $demande->setStatut('acceptee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($request->request->get('message_admin', ''));

        $entityManager->persist($affectation);
        // Persist et flush des changements (affectation + mise à jour de la demande)
        $entityManager->flush();

        // Notifier le bénévole que sa demande a été acceptée via le service centralisé
        $tacheNom = $tache->getTitre() ?? 'Tâche #' . $tache->getId();
        $notificationService->notifyBenevoleDemandeAcceptee(
            $demande->getBenevole()->getUtilisateur(),
            $tacheNom
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande de {$benevoleNom} a été acceptée.");
        return $this->redirectToRoute('admin_demandes_taches');
    }

    /**
     * Refuser une demande
     */
    #[Route('/admin/refuser-demande/{id}', name: 'admin_refuser_demande', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refuserDemande(
        int $id,
        Request $request,
        DemandeTacheRepository $demandeRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ): Response {
        $demande = $demandeRepository->find($id);
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
        if (!$this->isCsrfTokenValid('refuser_demande_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_demandes_taches');
        }

        $demande->setStatut('refusee');
        $demande->setDateReponse(new \DateTime());
        $demande->setAdminReponse($this->getUser());
        $demande->setMessageAdmin($request->request->get('message_admin', ''));

        // Sauvegarder les modifications de la demande
        $entityManager->flush();

        // Notifier le bénévole que sa demande a été refusée via le service centralisé
        $tacheNom = $demande->getTache()->getTitre() ?? 'Tâche #' . $demande->getTache()->getId();
        $messageAdmin = $request->request->get('message_admin', '');
        $notificationService->notifyBenevoleDemandeRefusee(
            $demande->getBenevole()->getUtilisateur(),
            $tacheNom,
            $messageAdmin ?: null
        );

        $benevoleNom = $demande->getBenevole()->getUtilisateur()->getPrenom() . ' ' . 
                       $demande->getBenevole()->getUtilisateur()->getNom();
        $this->addFlash('success', "La demande de {$benevoleNom} a été refusée.");
        return $this->redirectToRoute('admin_demandes_taches');
    }
}
