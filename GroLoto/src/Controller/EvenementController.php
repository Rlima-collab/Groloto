<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use App\Form\EvenementType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'app_evenements')]
    public function index(EvenementRepository $evenementRepo, TacheRepository $tacheRepo): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $now = new \DateTime();
        
        // Récupérer tous les événements
        $evenements = $evenementRepo->findAll();
        
        // Séparer les événements passés et futurs
        $evenementsPasses = [];
        $evenementsFuturs = [];
        $evenementsCeMois = [];
        
        // Calculer le début et la fin du mois actuel
        $debutMois = new \DateTime('first day of this month 00:00:00');
        $finMois = new \DateTime('last day of this month 23:59:59');
        
        foreach ($evenements as $evenement) {
            if ($evenement->getDateFin() < $now) {
                $evenementsPasses[] = $evenement;
            } else {
                $evenementsFuturs[] = $evenement;
            }
            
            // Vérifier si l'événement a lieu ce mois-ci
            if ($evenement->getDateDebut() >= $debutMois && $evenement->getDateDebut() <= $finMois) {
                $evenementsCeMois[] = $evenement;
            }
        }
        
        // Trier les événements
        usort($evenementsPasses, function($a, $b) {
            return $b->getDateFin() <=> $a->getDateFin(); // Plus récents en premier
        });
        
        usort($evenementsFuturs, function($a, $b) {
            return $a->getDateDebut() <=> $b->getDateDebut(); // Plus proches en premier
        });
        
        // Récupérer tous les tâches pour le calendrier
        $taches = $tacheRepo->findAll();
        $calendrierData = [];
        
        // Ajouter les tâches au calendrier
        foreach ($taches as $tache) {
            $calendrierData[] = [
                'id' => 'tache_' . $tache->getId(),
                'title' => $tache->getTitre() ?: ($tache->getEvenement() ? $tache->getEvenement()->getNom() : 'Tâche'),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#3b82f6',
                'className' => 'tache-event',
                'extendedProps' => [
                    'type' => 'tache',
                    'evenement' => $tache->getEvenement() ? $tache->getEvenement()->getNom() : null,
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque(),
                    'titre' => $tache->getTitre()
                ]
            ];
        }
        
        // Ajouter les événements au calendrier
        foreach ($evenements as $evenement) {
            $isPassé = $evenement->getDateFin() < $now;
            
            // Gérer les dates qui peuvent être DateTime ou DateTimeInterface
            $dateDebut = $evenement->getDateDebut();
            $dateFin = $evenement->getDateFin();
            
            // Si c'est une date simple, on ajoute l'heure par défaut
            if ($dateDebut instanceof \DateTime) {
                $startDate = $dateDebut->format('Y-m-d') . 'T09:00:00';
            } else {
                $startDate = $dateDebut->format('Y-m-d\TH:i:s');
            }
            
            if ($dateFin instanceof \DateTime) {
                $endDate = $dateFin->format('Y-m-d') . 'T18:00:00';
            } else {
                $endDate = $dateFin->format('Y-m-d\TH:i:s');
            }
            
            $calendrierData[] = [
                'id' => 'evenement_' . $evenement->getId(),
                'title' => $evenement->getNom(),
                'start' => $startDate,
                'end' => $endDate,
                'backgroundColor' => $isPassé ? '#6b7280' : '#10b981',
                'borderColor' => $isPassé ? '#6b7280' : '#10b981',
                'className' => 'evenement-event',
                'extendedProps' => [
                    'type' => 'evenement',
                    'description' => $evenement->getDescription(),
                    'lieu' => $evenement->getLieu()
                ]
            ];
        }
        
        // Statistiques
        $metriques = [
            'total' => count($evenements),
            'futurs' => count($evenementsFuturs),
            'passes' => count($evenementsPasses),
            'taches_total' => count($taches)
        ];
        
        return $this->render('evenements.html.twig', [
            'evenements_passes' => $evenementsPasses,
            'evenements_futurs' => $evenementsFuturs,
            'evenements_ce_mois' => count($evenementsCeMois),
            'taches' => json_encode($calendrierData),
            'metriques' => $metriques,
            'tous_evenements' => $evenements,
            'total_evenements' => count($evenements),
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/evenements/create', name: 'app_evenements_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateVendredi = $form->get('dateVendredi')->getData();
            $includeJM2 = $form->get('includeJM2')->getData();
            $includeJM1 = $form->get('includeJM1')->getData();
            $includeJP1 = $form->get('includeJP1')->getData();

            $dateDebut = clone $dateVendredi;
            $dateFin = clone $dateVendredi;
            $dateFin->modify('+2 days'); // Dimanche

            $offsetBefore = 0;
            if ($includeJM2) {
                $offsetBefore = 2;
            } elseif ($includeJM1) {
                $offsetBefore = 1;
            }
            if ($offsetBefore > 0) {
                $dateDebut->modify('-' . $offsetBefore . ' days');
            }

            if ($includeJP1) {
                $dateFin->modify('+1 day');
            }

            $evenement->setDateDebut($dateDebut);
            $evenement->setDateFin($dateFin);

            $em->persist($evenement);
            $em->flush();

            return $this->redirectToRoute('app_evenements');
        }

        return $this->render('evenement/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    private function getColorByStatus($tache): string
    {
        if (!$tache->getEvenement()) {
            return '#6c757d'; // Gris pour les tâches sans événement
        }
        
        $now = new \DateTime();
        $eventEnd = $tache->getEvenement()->getDateFin();
        
        if ($eventEnd < $now) {
            return '#28a745'; // Vert pour les événements passés
        } else {
            return '#007bff'; // Bleu pour les événements futurs
        }
    }
}