<?php

namespace App\Controller;

use App\Entity\Tache;
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
        
        // Pour chaque tâche, récupérer les bénévoles assignés
        $tachesData = [];
        foreach ($taches as $tache) {
            $affectations = $affectationRepository->findBy(['tache' => $tache]);
            $benevoles = [];
            foreach ($affectations as $affectation) {
                // On ne garde que les bénévoles réellement assignés (acceptés)
                if ($affectation->getStatut() !== 'assigne') {
                    continue;
                }

                $benevole = $affectation->getBenevole();
                if ($benevole && $benevole->getUtilisateur()) {
                    $nom = $benevole->getUtilisateur()->getPrenom() . ' ' . $benevole->getUtilisateur()->getNom();
                    $benevoles[] = $nom;
                }
            }
            
            $tachesData[] = [
                'tache' => $tache,
                'benevoles' => $benevoles,
                'nbBenevoles' => count($benevoles)
            ];
        }
        
        return $this->render('taches/index.html.twig', [
            'tachesData' => $tachesData,
            'filtreActif' => $filtre,
            'triActif' => $tri,
        ]);
    }

    #[Route('/new', name: 'tache_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            
            // Gestion des bénévoles sélectionnés
            $benevoles = $form->get('benevoles')->getData();
            foreach ($benevoles as $benevole) {
                $affectation = new AffectationTache();
                $affectation->setTache($tache);
                $affectation->setBenevole($benevole);
                $affectation->setUtilisateur($benevole->getUtilisateur());
                $affectation->setDateAffectation(new \DateTime());
                $affectation->setStatut('proposee');
                $entityManager->persist($affectation);

                // Notification
                $notificationService->notifyBenevoleProposition($benevole->getUtilisateur(), $tache->getTitre());
            }

            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été créée avec succès !');
            return $this->redirectToRoute('tache_index');
        }

        return $this->render('taches/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/check-availability', name: 'api_check_availability', methods: ['GET'])]
    public function checkAvailability(Request $request, AffectationTacheRepository $affectationRepository): JsonResponse
    {
        $startStr = $request->query->get('start');
        $endStr = $request->query->get('end');

        if (!$startStr || !$endStr) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        try {
            $start = new \DateTime($startStr);
            $end = new \DateTime($endStr);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format'], 400);
        }

        // Trouver tous les bénévoles qui ont une affectation "assigne" chevauchant cette période
        // On doit faire une requête custom car findOverlappingAssignments prend un bénévole spécifique
        // Ici on veut tous les bénévoles indisponibles d'un coup
        
        $indisponibles = $affectationRepository->createQueryBuilder('a')
            ->select('IDENTITY(a.benevole) as id')
            ->join('a.tache', 't')
            ->where('a.statut = :statut')
            ->andWhere('t.debut < :end')
            ->andWhere('t.fin > :start')
            ->setParameter('statut', 'assigne')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $ids = array_map(fn($row) => $row['id'], $indisponibles);

        return new JsonResponse(['unavailable_ids' => $ids]);
    }


    #[Route('/{id}/edit', name: 'tache_edit')]
    public function edit(int $id, Request $request, TacheRepository $tacheRepository, EntityManagerInterface $entityManager): Response
    {
        $tache = $tacheRepository->find($id);
        
        if (!$tache) {
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('tache_index');
        }

        $form = $this->createForm(TacheType::class, $tache, [
            'include_benevoles' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        // Traitement du formulaire (POST)
        if ($request->isMethod('POST')) {
            $benevolesIds = $request->request->all('benevoles'); // Array of IDs
            if (!empty($benevolesIds)) {
                foreach ($benevolesIds as $benevoleId) {
                    $benevole = $benevoleRepository->find($benevoleId);
                    if ($benevole) {
                        // Vérifier à nouveau la disponibilité pour être sûr
                        $chevauchements = $affectationRepository->findOverlappingAssignments($benevole, $tache->getDebut(), $tache->getFin());
                        $estPrisAilleurs = false;
                        foreach ($chevauchements as $chevauchement) {
                            if ($chevauchement->getTache()->getId() !== $tache->getId()) {
                                $estPrisAilleurs = true;
                                break;
                            }
                        }

                        if (!$estPrisAilleurs) {
                            // Vérifier si déjà affecté/proposé
                            $existing = $affectationRepository->findOneByTacheAndBenevole($tache, $benevole);
                            if (!$existing) {
                                $affectation = new AffectationTache();
                                $affectation->setTache($tache);
                                $affectation->setBenevole($benevole);
                                $affectation->setUtilisateur($benevole->getUtilisateur());
                                $affectation->setDateAffectation(new \DateTime());
                                $affectation->setStatut('proposee');
                                $entityManager->persist($affectation);

                                // Notification
                                $notificationService->notifyBenevoleProposition($benevole->getUtilisateur(), $tache->getTitre());
                            }
                        }
                    }
                }
                $entityManager->flush();
                $this->addFlash('success', 'Les propositions ont été envoyées.');
                return $this->redirectToRoute('tache_proposer', ['id' => $id]);
            }
        }

        if ($request->query->get('ajax')) {
            return $this->render('taches/_benevoles_list.html.twig', [
                'benevolesData' => $benevolesData,
            ]);
        }

        return $this->render('taches/proposer.html.twig', [
            'tache' => $tache,
            'benevolesData' => $benevolesData,
            'search' => $search
        ]);
    }
}
