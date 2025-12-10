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
use App\Service\NotificationService;
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
        // Récupérer les paramètres de filtre
        $filtre = $request->query->get('filtre', 'toutes'); // toutes, futures, en_cours, passees
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
                'benevolesAssignes' => $benevolesAssignes,
                'benevolesProposees' => $benevolesProposees,
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
    public function delete(int $id, Request $request, TacheRepository $tacheRepository, EntityManagerInterface $entityManager): Response
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
            $entityManager->remove($tache);
            $entityManager->flush();
            
            $this->addFlash('success', 'La tâche "' . $tache->getTitre() . '" a été supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la tâche.');
        }

        return $this->redirectToRoute('tache_index');
    }

    #[Route('/{id}/duplicate', name: 'tache_duplicate')]
    public function duplicate(int $id, TacheRepository $tacheRepository, EntityManagerInterface $entityManager): Response
    {
        $tacheSource = $tacheRepository->find($id);

        if (!$tacheSource) {
            $this->addFlash('error', 'Tâche source non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        $tache = new Tache();
        $tache->setTitre($tacheSource->getTitre());
        $tache->setWeekend($tacheSource->getWeekend());
        $tache->setMaxPersonnes($tacheSource->getMaxPersonnes());
        $tache->setRemarque($tacheSource->getRemarque());

        // Cloner les plages horaires
        foreach ($tacheSource->getPlagesHoraires() as $plageSrc) {
            $plage = new PlageHoraire();
            $plage->setJour($plageSrc->getJour());
            $plage->setHeureDebut($plageSrc->getHeureDebut());
            $plage->setHeureFin($plageSrc->getHeureFin());
            $plage->setMaxPersonnesPlage($plageSrc->getMaxPersonnesPlage());
            $plage->setTache($tache);
            $tache->addPlageHoraire($plage);
        }

        // Synchroniser debut/fin
        $tache = $this->syncTaskDates($tache);

        $entityManager->persist($tache);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a été dupliquée avec succès!');
        return $this->redirectToRoute('tache_edit', ['id' => $tache->getId()]);
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

            $benevolesData[] = [
                'benevole' => $benevole,
                'statut' => $statut,
                'affectation' => $affectationActuelle
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
}
