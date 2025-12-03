<?php

namespace App\Controller;

use App\Entity\Benevole;
use App\Form\BenevoleEditType;
use App\Repository\BenevoleRepository;
use App\Repository\TacheRepository;
use App\Repository\AffectationTacheRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\DemandeAnnulationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BenevoleController extends AbstractController
{
    #[Route('/benevoles', name: 'benevoles')]
    public function index(BenevoleRepository $benevoleRepository, TacheRepository $tacheRepository): Response
    {
        // Récupération des bénévoles
        $benevoles = $benevoleRepository->findActiveWithUser();
        
        // Ajouter le nombre de tâches réalisées pour chaque bénévole
        $benevolesAvecStats = [];
        foreach ($benevoles as $benevole) {
            $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());
            $benevolesAvecStats[] = [
                'benevole' => $benevole,
                'nb_taches_realisees' => count($tachesRealisees)
            ];
        }
        
        $totalBenevoles = $benevoleRepository->countActive();
        $nouveauxBenevoles = count($benevoleRepository->findRecentlyRegistered());

        $metriques = [
            'total' => $totalBenevoles,
            'nouveaux' => $nouveauxBenevoles,
            'actifs' => $totalBenevoles,
            'disponibles' => $totalBenevoles
        ];

        // Par défaut, on récupère les tâches futures globales
        $tachesBrutes = $tacheRepository->findTachesFutures();

        // Si l'utilisateur connecté est lié à un bénévole, on filtre pour ne récupérer
        // que ses tâches assignées via AffectationTache
        $user = $this->getUser();
        $benevoleForUser = null;
        if ($user) {
            // Rechercher un objet Benevole lié à cet utilisateur via le repository injecté
            $benevoleForUser = $benevoleRepository->findOneBy(['utilisateur' => $user]);
        }

        if ($benevoleForUser) {
            $tachesBrutes = $tacheRepository->findTachesFuturesForBenevole($benevoleForUser->getId());
            // Si aucune tâche future (p.ex. tâches en 2024), on renvoie toutes les tâches assignées
            if (empty($tachesBrutes)) {
                $tachesBrutes = $tacheRepository->findTachesForBenevole($benevoleForUser->getId());
            }
        }

        $taches = [];
        foreach ($tachesBrutes as $tache) {
            $taches[] = [
                'id' => $tache->getId(),
                'title' => $tache->getTitre(),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'extendedProps' => [
                    'weekend' => $tache->getWeekend()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Pour la liste "taches_proches" à droite, on applique le même filtrage
        if ($benevoleForUser) {
            $tachesProches = $tacheRepository->findTachesFuturesForBenevole($benevoleForUser->getId());
            if (empty($tachesProches)) {
                $tachesProches = $tacheRepository->findTachesForBenevole($benevoleForUser->getId());
            }
        } else {
            $tachesProches = $tacheRepository->findTachesFutures();
        }

        // Convertir les entités Tache en tableaux simples pour le template
        $tachesProchesData = [];
        foreach ($tachesProches as $tp) {
            $tachesProchesData[] = [
                'id' => $tp->getId(),
                'titre' => $tp->getTitre(),
                'debut' => $tp->getDebut(),
                'poste_requis' => $tp->getPosteRequis(),
            ];
        }

        // Récupérer les tâches réalisées si l'utilisateur est un bénévole
        $tachesRealiseesData = [];
        if ($benevoleForUser) {
            $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevoleForUser->getId());
            foreach ($tachesRealisees as $tr) {
                $tachesRealiseesData[] = [
                    'id' => $tr->getId(),
                    'titre' => $tr->getTitre(),
                    'debut' => $tr->getDebut(),
                    'fin' => $tr->getFin(),
                    'poste_requis' => $tr->getPosteRequis(),
                ];
            }
        }

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevolesAvecStats,
            'metriques' => $metriques,
            'taches' => $taches,
            // fournir des tableaux simples pour le template
            'taches_proches' => $tachesProchesData,
            'taches_realisees' => $tachesRealiseesData
        ]);
    }

    #[Route('/benevole/mon-planning', name: 'benevole_mon_planning')]
    public function monPlanning(
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository,
        AffectationTacheRepository $affectationRepository,
        DemandeAnnulationRepository $demandeAnnulationRepository
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole pour accéder à cette page.');
            return $this->redirectToRoute('benevoles');
        }

        // Récupérer les tâches du bénévole
        $tachesBrutes = $tacheRepository->findTachesFuturesForBenevole($benevole->getId());
        if (empty($tachesBrutes)) {
            $tachesBrutes = $tacheRepository->findTachesForBenevole($benevole->getId());
        }

        $taches = [];
        foreach ($tachesBrutes as $tache) {
            $taches[] = [
                'id' => $tache->getId(),
                'title' => $tache->getTitre(),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#1a3c5a',
                'borderColor' => '#2b5d8a',
                'extendedProps' => [
                    'weekend' => $tache->getWeekend()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Tâches à venir avec les infos d'affectation et de demande d'annulation
        $tachesProches = $tacheRepository->findTachesFuturesForBenevole($benevole->getId());
        if (empty($tachesProches)) {
            $tachesProches = $tacheRepository->findTachesForBenevole($benevole->getId());
        }

        $tachesProchesData = [];
        foreach ($tachesProches as $tp) {
            $affectation = $affectationRepository->findOneByTacheAndBenevole($tp, $benevole);
            $demandeEnAttente = null;
            if ($affectation) {
                $demandeEnAttente = $demandeAnnulationRepository->findByAffectationEnAttente($affectation);
            }
            
            $tachesProchesData[] = [
                'id' => $tp->getId(),
                'titre' => $tp->getTitre(),
                'debut' => $tp->getDebut(),
                'poste_requis' => $tp->getPosteRequis(),
                'affectation_id' => $affectation ? $affectation->getId() : null,
                'demande_annulation_en_attente' => $demandeEnAttente !== null,
            ];
        }

        // Tâches réalisées
        $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());
        $tachesRealiseesData = [];
        foreach ($tachesRealisees as $tr) {
            $tachesRealiseesData[] = [
                'id' => $tr->getId(),
                'titre' => $tr->getTitre(),
                'debut' => $tr->getDebut(),
                'fin' => $tr->getFin(),
                'poste_requis' => $tr->getPosteRequis(),
            ];
        }

        // Calcul des statistiques
        $totalTaches = count($tachesBrutes);
        $totalFutures = count($tachesProchesData);
        $totalRealisees = count($tachesRealiseesData);

        return $this->render('benevoles/mon_planning.html.twig', [
            'benevole' => $benevole,
            'taches' => json_encode($taches),
            'taches_proches' => $tachesProchesData,
            'taches_realisees' => $tachesRealiseesData,
            'total_taches' => $totalTaches,
            'total_futures' => $totalFutures,
            'total_realisees' => $totalRealisees
        ]);
    }

    #[Route('/benevoles/{id}/edit', name: 'benevole_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $utilisateur = $benevole->getUtilisateur();

        $form = $this->createForm(BenevoleEditType::class, $benevole);
        
        // Pré-remplir les champs utilisateur
        $form->get('prenom')->setData($utilisateur->getPrenom());
        $form->get('nom')->setData($utilisateur->getNom());
        $form->get('email')->setData($utilisateur->getEmail());
        $form->get('telephone')->setData($utilisateur->getTelephone());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPrenom($form->get('prenom')->getData());
            $utilisateur->setNom($form->get('nom')->getData());
            $utilisateur->setEmail($form->get('email')->getData());
            $utilisateur->setTelephone($form->get('telephone')->getData());
            $utilisateur->setDateModification(new \DateTime());

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le bénévole a été mis à jour avec succès !');
                return $this->redirectToRoute('benevoles');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du bénévole.');
            }
        }

        return $this->render('benevoles/edit.html.twig', [
            'form' => $form->createView(),
            'benevole' => $benevole,
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/benevoles/{id}/delete', name: 'benevole_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_benevole_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevoles');
        }

        try {
            $utilisateur = $benevole->getUtilisateur();
            $nomComplet = $utilisateur->getPrenom() . ' ' . $utilisateur->getNom();
            
            $entityManager->remove($benevole);
            $entityManager->flush();
            
            $this->addFlash('success', "Le bénévole {$nomComplet} a été supprimé avec succès.");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du bénévole.');
        }

        return $this->redirectToRoute('benevoles');
    }

    #[Route('/benevoles/{id}/planning', name: 'benevole_planning')]
    #[IsGranted('ROLE_ADMIN')]
    public function planning(
        int $id,
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $utilisateur = $benevole->getUtilisateur();

        // Récupérer les tâches du bénévole
        $tachesBrutes = $tacheRepository->findTachesForBenevole($benevole->getId());

        $taches = [];
        foreach ($tachesBrutes as $tache) {
            $taches[] = [
                'id' => $tache->getId(),
                'title' => $tache->getTitre(),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#0d1b2a',
                'borderColor' => '#1a3c5a',
                'extendedProps' => [
                    'weekend' => $tache->getWeekend()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Tâches futures
        $tachesFutures = $tacheRepository->findTachesFuturesForBenevole($benevole->getId());
        
        // Tâches réalisées
        $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());

        return $this->render('benevoles/planning.html.twig', [
            'benevole' => $benevole,
            'utilisateur' => $utilisateur,
            'taches' => json_encode($taches),
            'taches_futures' => $tachesFutures,
            'taches_realisees' => $tachesRealisees,
            'total_taches' => count($tachesBrutes),
            'total_futures' => count($tachesFutures),
            'total_realisees' => count($tachesRealisees)
        ]);
    }

    #[Route('/benevole/propositions', name: 'benevole_propositions')]
    public function propositions(
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository,
        AffectationTacheRepository $affectationRepository,
        UtilisateurRepository $utilisateurRepository,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole.');
            return $this->redirectToRoute('benevoles');
        }

        // Traitement des actions (Accepter/Refuser)
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $tacheId = $request->request->get('tache_id');
            $tache = $tacheRepository->find($tacheId);
            
            if ($tache) {
                $affectation = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
                if ($affectation && $affectation->getStatut() === 'proposee') {
                    if ($action === 'accepter') {
                        // Vérifier s'il reste des places disponibles
                        $maxPersonnes = $tache->getMaxPersonnes();
                        if ($maxPersonnes !== null) {
                            $nbAssignes = $affectationRepository->countBenevolesAssignesByTache($tache->getId());
                            if ($nbAssignes >= $maxPersonnes) {
                                $this->addFlash('error', 'Désolé, il n\'y a plus de place disponible pour cette tâche. Le nombre maximum de bénévoles (' . $maxPersonnes . ') a été atteint.');
                                return $this->redirectToRoute('benevole_propositions');
                            }
                        }

                        // Vérifier chevauchement avant d'accepter
                        $chevauchements = $affectationRepository->findOverlappingAssignments($benevole, $tache->getDebut(), $tache->getFin());
                        $estPrisAilleurs = false;
                        foreach ($chevauchements as $chevauchement) {
                            if ($chevauchement->getTache()->getId() !== $tache->getId() && $chevauchement->getStatut() === 'assigne') {
                                $estPrisAilleurs = true;
                                break;
                            }
                        }
                        
                        if ($estPrisAilleurs) {
                            $this->addFlash('error', 'Vous ne pouvez pas accepter cette tâche car vous avez déjà une tâche validée sur ce créneau.');
                        } else {
                            $affectation->setStatut('assigne');
                            $entityManager->flush();
                            $this->addFlash('success', 'Vous avez accepté la tâche.');

                            // Notifier les admins
                            $admins = $utilisateurRepository->findByRoleName('admin');
                            foreach ($admins as $admin) {
                                $notificationService->notifyAdminPropositionAcceptee(
                                    $admin,
                                    $user->getPrenom() . ' ' . $user->getNom(),
                                    $tache->getTitre()
                                );
                            }
                        }
                    } elseif ($action === 'refuser') {
                        $affectation->setStatut('refusee');
                        $entityManager->flush();
                        $this->addFlash('info', 'Vous avez refusé la tâche.');

                        // Notifier les admins (optionnel, mais utile)
                        $admins = $utilisateurRepository->findByRoleName('admin');
                        foreach ($admins as $admin) {
                            $notificationService->notifyAdminPropositionRefusee(
                                $admin,
                                $user->getPrenom() . ' ' . $user->getNom(),
                                $tache->getTitre()
                            );
                        }
                    }
                }
            }
            return $this->redirectToRoute('benevole_propositions');
        }

        $propositions = $tacheRepository->findPropositionsForBenevole($benevole->getId());

        // Calculer les places disponibles pour chaque proposition
        $placesDisponibles = [];
        foreach ($propositions as $tache) {
            $maxPersonnes = $tache->getMaxPersonnes();
            if ($maxPersonnes !== null) {
                $nbAssignes = $affectationRepository->countBenevolesAssignesByTache($tache->getId());
                $placesDisponibles[$tache->getId()] = max(0, $maxPersonnes - $nbAssignes);
            } else {
                $placesDisponibles[$tache->getId()] = null; // Illimité
            }
        }

        return $this->render('benevoles/propositions.html.twig', [
            'propositions' => $propositions,
            'places_disponibles' => $placesDisponibles
        ]);
    }
}