<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Entity\PlageHoraire;
use App\Entity\AffectationTache;
use App\Form\TacheType;
use App\Form\TacheAffectationType;
use App\Repository\TacheRepository;
use App\Repository\AffectationTacheRepository;
use App\Repository\BenevoleRepository;
use App\Repository\DisponibiliteWeekendRepository;
use App\Service\NotificationService;
use App\Service\BatchTaskAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/taches')]
#[IsGranted('ROLE_ADMIN')]
class TacheController extends AbstractController
{
    #[Route('', name: 'tache_index')]
    public function index(Request $request, TacheRepository $tacheRepository, AffectationTacheRepository $affectationRepository): Response
    {
        // Récupérer les paramètres de filtre (défaut: futures)
        $filtre = $request->query->get('filtre', 'futures'); // futures (défaut), toutes, en_cours, passees
        $tri = $request->query->get('tri', 'date_proche'); // date_proche, date_eloignee, titre
        
        // Récupérer les tâches selon le filtre
        $now = new \DateTime();
        $queryBuilder = $tacheRepository->createQueryBuilder('t')
            ->leftJoin('t.weekend', 'w')
            ->addSelect('w');
        
        switch ($filtre) {
            case 'futures':
                $queryBuilder->andWhere('t.debut > :now')
                    ->setParameter('now', $now);
                break;
            case 'en_cours':
                $queryBuilder->andWhere('t.debut <= :now')
                    ->andWhere('t.fin >= :now')
                    ->setParameter('now', $now);
                break;
            case 'passees':
                $queryBuilder->andWhere('t.fin < :now')
                    ->setParameter('now', $now);
                break;
            // 'toutes' : pas de filtre
        }
        
        // Appliquer le tri
        switch ($tri) {
            case 'date_proche':
                $queryBuilder->orderBy('t.debut', 'ASC');
                break;
            case 'date_eloignee':
                $queryBuilder->orderBy('t.debut', 'DESC');
                break;
            case 'titre':
                $queryBuilder->orderBy('t.titre', 'ASC');
                break;
        }
        
        $taches = $queryBuilder->getQuery()->getResult();
        
        // Pour chaque tâche, récupérer les bénévoles assignés et proposés
        $tachesData = [];
        foreach ($taches as $tache) {
            $affectations = $affectationRepository->findBy(['tache' => $tache]);
            $benevolesAssignes = [];
            $benevolesProposees = [];
            
            foreach ($affectations as $affectation) {
                $benevole = $affectation->getBenevole();
                if ($benevole && $benevole->getUtilisateur()) {
                    $nom = $benevole->getUtilisateur()->getPrenom() . ' ' . $benevole->getUtilisateur()->getNom();
                    
                    if ($affectation->getStatut() === 'assigne') {
                        $benevolesAssignes[] = $nom;
                    } elseif ($affectation->getStatut() === 'proposee') {
                        $benevolesProposees[] = $nom;
                    }
                }
            }
            
            $nbAssignes = count($benevolesAssignes);
            $nbProposees = count($benevolesProposees);
            $maxPersonnes = $tache->getMaxPersonnes();
            $placesRestantes = $maxPersonnes ? max(0, $maxPersonnes - $nbAssignes) : null;
            
            $tachesData[] = [
                'tache' => $tache,
                'nbAssignes' => $nbAssignes,
                'nbProposees' => $nbProposees,
                'placesRestantes' => $placesRestantes,
                'nbBenevoles' => $nbAssignes // Pour compatibilité
            ];
        }
        
        return $this->render('taches/index.html.twig', [
            'tachesData' => $tachesData,
            'filtreActif' => $filtre,
            'triActif' => $tri,
        ]);
    }

    #[Route('/new', name: 'tache_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all()['tache'] ?? [];
            
            // Créer une plage horaire avec le jour et les horaires spécifiés
            if (isset($formData['jour_plage']) && !empty($formData['jour_plage'])) {
                $plage = new PlageHoraire();
                $plage->setJour(new \DateTime($formData['jour_plage']));
                $plage->setHeureDebut(new \DateTime($formData['heure_debut_plage']));
                $plage->setHeureFin(new \DateTime($formData['heure_fin_plage']));
                $plage->setTache($tache);
                $tache->addPlageHoraire($plage);
            }
            
            // Synchroniser debut/fin avec les plages horaires
            $tache = $this->syncTaskDates($tache);
            
            $entityManager->persist($tache);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été créée avec succès !');
            return $this->redirectToRoute('tache_index');
        }

        return $this->render('taches/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'tache_edit')]
    public function edit(int $id, Request $request, TacheRepository $tacheRepository, EntityManagerInterface $entityManager): Response
    {
        $tache = $tacheRepository->find($id);
        
        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Synchroniser debut/fin avec les plages horaires
            $tache = $this->syncTaskDates($tache);
            
            $entityManager->flush();
            
            $this->addFlash('success', 'La tâche a été mise à jour avec succès !');
            return $this->redirectToRoute('tache_index');
        }

        return $this->render('taches/edit.html.twig', [
            'form' => $form->createView(),
            'tache' => $tache,
        ]);
    }

    #[Route('/{id}/delete', name: 'tache_delete', methods: ['POST'])]
    public function delete(
        int $id, 
        Request $request, 
        TacheRepository $tacheRepository, 
        AffectationTacheRepository $affectationRepository,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response
    {
        $tache = $tacheRepository->find($id);

        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_tache_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('tache_index');
        }

        try {
            // Récupérer les affectations pour notifier les bénévoles avant la suppression
            $affectations = $affectationRepository->findBy(['tache' => $tache]);
            $tacheNom = $tache->getTitre();
            
            // Notifier tous les bénévoles assignés ou proposés que la tâche est supprimée
            foreach ($affectations as $affectation) {
                $benevole = $affectation->getBenevole();
                if ($benevole && $benevole->getUtilisateur()) {
                    $notificationService->notifyBenevoleTacheSupprimee($benevole->getUtilisateur(), $tacheNom);
                }
            }
            
            $entityManager->remove($tache);
            $entityManager->flush();
            
            $this->addFlash('success', 'La tâche "' . $tacheNom . '" a été supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la tâche.');
        }

        return $this->redirectToRoute('tache_index');
    }

    #[Route('/{id}/benevoles', name: 'tache_benevoles')]
    public function benevoles(int $id, TacheRepository $tacheRepository, AffectationTacheRepository $affectationRepository): Response
    {
        $tache = $tacheRepository->find($id);

        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        // Récupérer les affectations pour cette tâche
        $affectations = $affectationRepository->findBy(['tache' => $tache]);

        $benevolesAssignes = [];
        $benevolesProposees = [];

        foreach ($affectations as $affectation) {
            $benevole = $affectation->getBenevole();
            if ($benevole && $benevole->getUtilisateur()) {
                $data = [
                    'benevole' => $benevole,
                    'utilisateur' => $benevole->getUtilisateur(),
                    'affectation' => $affectation
                ];

                if ($affectation->getStatut() === 'assigne') {
                    $benevolesAssignes[] = $data;
                } elseif ($affectation->getStatut() === 'proposee') {
                    $benevolesProposees[] = $data;
                }
            }
        }

        return $this->render('taches/benevoles.html.twig', [
            'tache' => $tache,
            'benevolesAssignes' => $benevolesAssignes,
            'benevolesProposees' => $benevolesProposees,
        ]);
    }

    #[Route('/{id}/retirer-benevole/{benevoleId}', name: 'tache_retirer_benevole', methods: ['POST'])]
    public function retirerBenevole(
        int $id,
        int $benevoleId,
        Request $request,
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        AffectationTacheRepository $affectationRepository,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response {
        $tache = $tacheRepository->find($id);

        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        $benevole = $benevoleRepository->find($benevoleId);
        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('tache_benevoles', ['id' => $id]);
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('retirer_benevole_' . $id . '_' . $benevoleId, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('tache_benevoles', ['id' => $id]);
        }

        // Trouver l'affectation
        $affectation = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
        
        if ($affectation) {
            $benevoleNom = $benevole->getUtilisateur()->getPrenom() . ' ' . $benevole->getUtilisateur()->getNom();
            $tacheNom = $tache->getTitre();
            
            // Supprimer l'affectation
            $entityManager->remove($affectation);
            $entityManager->flush();
            
            // Notifier le bénévole qu'il a été retiré de la tâche
            if ($benevole->getUtilisateur()) {
                $notificationService->notifyBenevoleRetireDeTache($benevole->getUtilisateur(), $tacheNom);
            }
            
            $this->addFlash('success', $benevoleNom . ' a été retiré(e) de la tâche "' . $tacheNom . '".');
        } else {
            $this->addFlash('error', 'Ce bénévole n\'est pas affecté à cette tâche.');
        }

        return $this->redirectToRoute('tache_benevoles', ['id' => $id]);
    }

    #[Route('/{id}/duplicate', name: 'tache_duplicate')]
    public function duplicate(int $id, Request $request, TacheRepository $tacheRepository, EntityManagerInterface $entityManager): Response
    {
        $tacheSource = $tacheRepository->find($id);

        if (!$tacheSource) {
            $this->addFlash('error', 'Tâche source non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        // Créer une nouvelle tâche pré-remplie avec les données de la source
        $tache = new Tache();
        $tache->setTitre($tacheSource->getTitre() . ' (copie)');
        $tache->setWeekend($tacheSource->getWeekend());
        $tache->setMaxPersonnes($tacheSource->getMaxPersonnes());
        $tache->setRemarque($tacheSource->getRemarque());

        // Créer le formulaire avec les données pré-remplies
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all()['tache'] ?? [];
            
            // Créer une plage horaire avec le jour et les horaires spécifiés
            if (isset($formData['jour_plage']) && !empty($formData['jour_plage'])) {
                $plage = new PlageHoraire();
                $plage->setJour(new \DateTime($formData['jour_plage']));
                $plage->setHeureDebut(new \DateTime($formData['heure_debut_plage']));
                $plage->setHeureFin(new \DateTime($formData['heure_fin_plage']));
                $plage->setTache($tache);
                $tache->addPlageHoraire($plage);
            }
            
            // Synchroniser debut/fin avec les plages horaires
            $tache = $this->syncTaskDates($tache);
            
            $entityManager->persist($tache);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été dupliquée avec succès !');
            return $this->redirectToRoute('tache_index');
        }

        // Préparer les données des plages horaires de la source pour le template
        $plagesSource = [];
        foreach ($tacheSource->getPlagesHoraires() as $plage) {
            $plagesSource[] = [
                'jour' => $plage->getJour(),
                'heureDebut' => $plage->getHeureDebut(),
                'heureFin' => $plage->getHeureFin(),
            ];
        }

        return $this->render('taches/duplicate.html.twig', [
            'form' => $form->createView(),
            'tacheSource' => $tacheSource,
            'plagesSource' => $plagesSource,
        ]);
    }

    #[Route('/{id}/affectation', name: 'tache_affectation')]
    public function affectation(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        AffectationTacheRepository $affectationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $tache = $tacheRepository->find($id);

        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        // Récupérer les affectations actuelles
        $affectationsActuelles = $affectationRepository->findBy(['tache' => $tache]);
        $benevolesActuels = [];
        foreach ($affectationsActuelles as $affectation) {
            $benevolesActuels[] = $affectation->getBenevole();
        }

        $form = $this->createForm(TacheAffectationType::class);
        $form->get('benevoles')->setData($benevolesActuels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $benevolesSelectionnes = $form->get('benevoles')->getData();

            // Vérifier la limite de personnes
            if ($tache->getMaxPersonnes() && count($benevolesSelectionnes) > $tache->getMaxPersonnes()) {
                $this->addFlash('error', sprintf(
                    'Vous ne pouvez pas assigner plus de %d bénévole(s) à cette tâche. Vous avez sélectionné %d bénévole(s).',
                    $tache->getMaxPersonnes(),
                    count($benevolesSelectionnes)
                ));
                
                return $this->render('taches/affectation.html.twig', [
                    'form' => $form->createView(),
                    'tache' => $tache,
                    'benevolesActuels' => $benevolesActuels,
                ]);
            }

            // Supprimer toutes les affectations existantes
            foreach ($affectationsActuelles as $affectation) {
                $entityManager->remove($affectation);
            }

            // Créer les nouvelles affectations
            foreach ($benevolesSelectionnes as $benevole) {
                $affectation = new AffectationTache();
                $affectation->setTache($tache);
                $affectation->setBenevole($benevole);
                $affectation->setUtilisateur($benevole->getUtilisateur());
                $affectation->setDateAffectation(new \DateTime());
                $affectation->setStatut('assigne'); // Statut par défaut
                $entityManager->persist($affectation);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les affectations ont été mises à jour avec succès !');
            return $this->redirectToRoute('tache_index');
        }

        return $this->render('taches/affectation.html.twig', [
            'form' => $form->createView(),
            'tache' => $tache,
            'benevolesActuels' => $benevolesActuels,
        ]);
    }

    #[Route('/{id}/proposer', name: 'tache_proposer')]
    public function proposer(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        BenevoleRepository $benevoleRepository,
        AffectationTacheRepository $affectationRepository,
        DisponibiliteWeekendRepository $disponibiliteRepo,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response {
        $tache = $tacheRepository->find($id);
        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        // Traitement des actions (POST)
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $benevoleId = $request->request->get('benevole_id');
            
            if ($benevoleId && $action) {
                $benevole = $benevoleRepository->find($benevoleId);
                if ($benevole) {
                    $existingAffectation = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
                    
                    switch ($action) {
                        case 'assigner':
                            if (!$existingAffectation) {
                                // Vérifier le max de personnes
                                $nbAssignes = $affectationRepository->countByTacheAndStatut($tache, 'assigne');
                                if ($tache->getMaxPersonnes() && $nbAssignes >= $tache->getMaxPersonnes()) {
                                    $this->addFlash('error', 'Le nombre maximum de bénévoles pour cette tâche est atteint.');
                                    break;
                                }
                                
                                $affectation = new AffectationTache();
                                $affectation->setTache($tache);
                                $affectation->setBenevole($benevole);
                                $affectation->setUtilisateur($benevole->getUtilisateur());
                                $affectation->setDateAffectation(new \DateTime());
                                $affectation->setStatut('assigne');
                                $entityManager->persist($affectation);
                                $entityManager->flush();
                                
                                // Notification
                                $notificationService->notifyBenevoleAssigne($benevole->getUtilisateur(), $tache->getTitre());
                                $this->addFlash('success', $benevole->getUtilisateur()->getPrenom() . ' a été assigné à la tâche.');
                            }
                            break;
                            
                        case 'proposer':
                            if (!$existingAffectation) {
                                // Vérifier le max de personnes
                                $nbAssignes = $affectationRepository->countByTacheAndStatut($tache, 'assigne');
                                if ($tache->getMaxPersonnes() && $nbAssignes >= $tache->getMaxPersonnes()) {
                                    $this->addFlash('error', 'Le nombre maximum de bénévoles pour cette tâche est atteint.');
                                    break;
                                }
                                
                                $affectation = new AffectationTache();
                                $affectation->setTache($tache);
                                $affectation->setBenevole($benevole);
                                $affectation->setUtilisateur($benevole->getUtilisateur());
                                $affectation->setDateAffectation(new \DateTime());
                                $affectation->setStatut('proposee');
                                $entityManager->persist($affectation);
                                $entityManager->flush();
                                
                                // Notification
                                $notificationService->notifyBenevoleProposition($benevole->getUtilisateur(), $tache->getTitre());
                                $this->addFlash('success', 'Proposition envoyée à ' . $benevole->getUtilisateur()->getPrenom() . '.');
                            }
                            break;
                            
                        case 'retirer':
                            if ($existingAffectation) {
                                $entityManager->remove($existingAffectation);
                                $entityManager->flush();
                                $this->addFlash('success', $benevole->getUtilisateur()->getPrenom() . ' a été retiré de la tâche.');
                            }
                            break;
                    }
                }
                return $this->redirectToRoute('tache_proposer', ['id' => $id]);
            }
        }

        // Recherche
        $search = $request->query->get('q');
        $qb = $benevoleRepository->createQueryBuilder('b')
            ->innerJoin('b.utilisateur', 'u')
            ->addSelect('u')
            ->where('b.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('u.nom', 'ASC');

        if ($search) {
            $qb->andWhere('(u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }
        
        $tousBenevoles = $qb->getQuery()->getResult();

        // Déterminer le créneau horaire de la tâche (matin/après-midi/soir)
        $tacheDebut = $tache->getDebut();
        $tacheFin = $tache->getFin();
        $weekend = $tache->getWeekend();
        
        $creneauTache = null;
        if ($tacheDebut) {
            $heure = (int) $tacheDebut->format('H');
            if ($heure < 12) {
                $creneauTache = 'matin';
            } elseif ($heure < 18) {
                $creneauTache = 'apres_midi';
            } else {
                $creneauTache = 'soir';
            }
        }
        
        $jourTache = $tacheDebut ? $tacheDebut->format('Y-m-d') : null;

        // Préparer les données pour la vue
        $benevolesData = [];
        foreach ($tousBenevoles as $benevole) {
            // Vérifier si déjà affecté à CETTE tâche
            $affectationActuelle = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
            
            // Vérifier chevauchement
            $chevauchements = $affectationRepository->findOverlappingAssignments($benevole, $tache->getDebut(), $tache->getFin());
            
            $estPrisAilleurs = false;
            foreach ($chevauchements as $chevauchement) {
                if ($chevauchement->getTache()->getId() !== $tache->getId()) {
                    $estPrisAilleurs = true;
                    break;
                }
            }

            $statut = 'disponible';
            if ($affectationActuelle) {
                $statut = $affectationActuelle->getStatut(); // 'assigne', 'proposee', etc.
            } elseif ($estPrisAilleurs) {
                $statut = 'indisponible';
            }
            
            // Vérifier les disponibilités renseignées par le bénévole
            $disponibiliteInfo = null;
            $matchCreneau = null;
            $aRenseigneDispos = false;
            
            if ($weekend && $jourTache) {
                // Chercher la disponibilité pour ce jour
                $dispos = $disponibiliteRepo->findByBenevoleAndWeekend($benevole, $weekend);
                
                // Le bénévole a-t-il renseigné des disponibilités pour ce weekend ?
                $aRenseigneDispos = !empty($dispos);
                
                foreach ($dispos as $dispo) {
                    if ($dispo->getJour() && $dispo->getJour()->format('Y-m-d') === $jourTache) {
                        $disponibiliteInfo = $dispo;
                        
                        // Vérifier si le créneau correspond
                        if ($creneauTache) {
                            if ($creneauTache === 'matin' && $dispo->isMatin()) {
                                $matchCreneau = true;
                            } elseif ($creneauTache === 'apres_midi' && $dispo->isApresMidi()) {
                                $matchCreneau = true;
                            } elseif ($creneauTache === 'soir' && $dispo->isSoir()) {
                                $matchCreneau = true;
                            } else {
                                $matchCreneau = false;
                            }
                        }
                        break;
                    }
                }
            }

            $benevolesData[] = [
                'benevole' => $benevole,
                'statut' => $statut,
                'affectation' => $affectationActuelle,
                'disponibilite' => $disponibiliteInfo,
                'matchCreneau' => $matchCreneau,
                'creneauTache' => $creneauTache,
                'aRenseigneDispos' => $aRenseigneDispos
            ];
        }

        // Calculer les statistiques
        $nbAssignes = $affectationRepository->countByTacheAndStatut($tache, 'assigne');
        $nbProposees = $affectationRepository->countByTacheAndStatut($tache, 'proposee');

        if ($request->query->get('ajax')) {
            return $this->render('taches/_benevoles_list.html.twig', [
                'benevolesData' => $benevolesData,
            ]);
        }

        return $this->render('taches/proposer.html.twig', [
            'tache' => $tache,
            'benevolesData' => $benevolesData,
            'search' => $search,
            'nbAssignes' => $nbAssignes,
            'nbProposees' => $nbProposees
        ]);
    }

    /**
     * Synchronise les champs debut/fin de la tâche avec les plages horaires
     */
    private function syncTaskDates(Tache $tache): Tache
    {
        if ($tache->getPlagesHoraires()->isEmpty()) {
            $tache->setDebut(null);
            $tache->setFin(null);
            return $tache;
        }

        $plages = $tache->getPlagesHoraires()->toArray();
        
        // Récupérer le début le plus tôt
        $debut = null;
        foreach ($plages as $plage) {
            $plageDebut = $plage->getDebut();
            if ($debut === null || $plageDebut < $debut) {
                $debut = $plageDebut;
            }
        }

        // Récupérer la fin la plus tard
        $fin = null;
        foreach ($plages as $plage) {
            $plageFin = $plage->getFin();
            if ($fin === null || $plageFin > $fin) {
                $fin = $plageFin;
            }
        }

        $tache->setDebut($debut);
        $tache->setFin($fin);
        
        return $tache;
    }

    /**
     * Page de gestion groupée des bénévoles
     */
    #[Route('/benevoles-management', name: 'tache_benevoles_management')]
    public function benevolesManagement(
        BenevoleRepository $benevoleRepo,
        AffectationTacheRepository $affectationRepo
    ): Response {
        $benevoles = $benevoleRepo->findAll();
        
        // Pour chaque bénévole, compter ses tâches
        $benevolesData = [];
        foreach ($benevoles as $benevole) {
            $user = $benevole->getUtilisateur();
            if (!$user) continue;

            $affectations = $affectationRepo->findBy(['benevole' => $benevole, 'statut' => 'assigne']);
            
            $benevolesData[] = [
                'benevole' => $benevole,
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'nbTaches' => count($affectations)
            ];
        }

        return $this->render('taches/benevoles_management.html.twig', [
            'benevolesData' => $benevolesData
        ]);
    }

    /**
     * API : Récupère les tâches disponibles pour un bénévole (avec détection conflits)
     */
    #[Route('/api/benevole/{id}/available-tasks', name: 'tache_api_available_tasks', methods: ['GET'])]
    public function getAvailableTasksForBenevole(
        int $id,
        BenevoleRepository $benevoleRepo,
        TacheRepository $tacheRepo,
        BatchTaskAssignmentService $batchService
    ): JsonResponse {
        $benevole = $benevoleRepo->find($id);
        if (!$benevole) {
            return $this->json(['error' => 'Bénévole non trouvé'], 404);
        }

        // Récupérer toutes les tâches futures
        $now = new \DateTime();
        $taches = $tacheRepo->createQueryBuilder('t')
            ->where('t.debut > :now')
            ->setParameter('now', $now)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();

        // Vérifier conflits
        $tasksData = $batchService->getAvailableTasksWithConflicts($benevole, $taches);

        // Formater pour JSON
        $result = [];
        foreach ($tasksData as $data) {
            $tache = $data['tache'];
            $result[] = [
                'id' => $tache->getId(),
                'titre' => $tache->getTitre(),
                'debut' => $tache->getDebut()?->format('d/m/Y H:i'),
                'fin' => $tache->getFin()?->format('d/m/Y H:i'),
                'hasConflict' => $data['hasConflict'],
                'weekendNom' => $tache->getWeekend()?->getNom()
            ];
        }

        return $this->json($result);
    }

    /**
     * Récupérer les tâches assignées à un bénévole (pour suppression)
     */
    #[Route('/api/benevole/{id}/assigned-tasks', name: 'tache_api_benevole_assigned', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAssignedTasksForBenevole(
        int $id,
        BenevoleRepository $benevoleRepo,
        AffectationTacheRepository $affectationRepo
    ): Response {
        $benevole = $benevoleRepo->find($id);
        if (!$benevole) {
            return $this->json(['error' => 'Bénévole non trouvé'], 404);
        }

        // Récupérer toutes les affectations du bénévole
        $affectations = $affectationRepo->findBy([
            'benevole' => $benevole,
            'statut' => 'assigne'
        ]);

        $result = [];
        foreach ($affectations as $affectation) {
            $tache = $affectation->getTache();
            $result[] = [
                'id' => $tache->getId(),
                'titre' => $tache->getTitre(),
                'debut' => $tache->getDebut()?->format('d/m/Y H:i'),
                'fin' => $tache->getFin()?->format('d/m/Y H:i'),
                'weekendNom' => $tache->getWeekend()?->getNom()
            ];
        }

        return $this->json($result);
    }

    /**
     * Assigner/proposer plusieurs tâches à un bénévole
     */
    #[Route('/benevole/{id}/batch-assign', name: 'tache_batch_assign', methods: ['POST'])]
    public function batchAssignTasks(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepo,
        BatchTaskAssignmentService $batchService
    ): Response {
        $benevole = $benevoleRepo->find($id);
        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé');
            return $this->redirectToRoute('tache_benevoles_management');
        }

        $data = json_decode($request->getContent(), true);
        $tasksData = $data['tasks'] ?? [];

        if (empty($tasksData)) {
            $this->addFlash('warning', 'Aucune tâche sélectionnée');
            return $this->redirectToRoute('tache_benevoles_management');
        }

        $admin = $this->getUser();
        $stats = $batchService->batchAssignTasks($benevole, $tasksData, $admin);

        if ($stats['success'] > 0) {
            $this->addFlash('success', sprintf(
                '%d tâche(s) traitée(s) avec succès',
                $stats['success']
            ));
        }

        if ($stats['conflicts'] > 0) {
            $this->addFlash('warning', sprintf(
                '%d tâche(s) ignorée(s) (conflit horaire)',
                $stats['conflicts']
            ));
        }

        if (!empty($stats['errors'])) {
            foreach ($stats['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Supprimer plusieurs tâches d'un bénévole
     */
    #[Route('/benevole/{id}/batch-remove', name: 'tache_batch_remove', methods: ['POST'])]
    public function batchRemoveTasks(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepo,
        BatchTaskAssignmentService $batchService
    ): Response {
        $benevole = $benevoleRepo->find($id);
        if (!$benevole) {
            return $this->json(['error' => 'Bénévole non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $tacheIds = $data['tache_ids'] ?? [];

        $removed = $batchService->batchRemoveTasks($benevole, $tacheIds);

        return $this->json([
            'success' => true,
            'count' => $removed,
            'removed' => $removed,
            'message' => sprintf('%d tâche(s) retirée(s)', $removed)
        ]);
    }
}
