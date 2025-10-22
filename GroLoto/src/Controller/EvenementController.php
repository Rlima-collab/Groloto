<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use App\Repository\CreneauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'app_evenements')]
    public function index(EvenementRepository $evenementRepo, CreneauRepository $creneauRepo): Response
    {
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
        
        // Récupérer tous les créneaux pour le calendrier
        $creneaux = $creneauRepo->findAll();
        $calendrierData = [];
        
        // Ajouter les créneaux au calendrier
        foreach ($creneaux as $creneau) {
            $calendrierData[] = [
                'id' => 'creneau_' . $creneau->getId(),
                'title' => $creneau->getTitre() ?: ($creneau->getEvenement() ? $creneau->getEvenement()->getNom() : 'Créneau'),
                'start' => $creneau->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $creneau->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#3b82f6',
                'className' => 'creneau-event',
                'extendedProps' => [
                    'type' => 'creneau',
                    'evenement' => $creneau->getEvenement() ? $creneau->getEvenement()->getNom() : null,
                    'poste_requis' => $creneau->getPosteRequis(),
                    'max_personnes' => $creneau->getMaxPersonnes(),
                    'remarques' => $creneau->getRemarque(),
                    'titre' => $creneau->getTitre()
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
            'creneaux_total' => count($creneaux)
        ];
        
        return $this->render('evenements.html.twig', [
            'evenements_passes' => $evenementsPasses,
            'evenements_futurs' => $evenementsFuturs,
            'evenements_ce_mois' => count($evenementsCeMois),
            'creneaux' => json_encode($calendrierData),
            'metriques' => $metriques,
            'tous_evenements' => $evenements,
            'total_evenements' => count($evenements)
        ]);
    }
    
    private function getColorByStatus($creneau): string
    {
        if (!$creneau->getEvenement()) {
            return '#6c757d'; // Gris pour les créneaux sans événement
        }
        
        $now = new \DateTime();
        $eventEnd = $creneau->getEvenement()->getDateFin();
        
        if ($eventEnd < $now) {
            return '#28a745'; // Vert pour les événements passés
        } else {
            return '#007bff'; // Bleu pour les événements futurs
        }
    }
}