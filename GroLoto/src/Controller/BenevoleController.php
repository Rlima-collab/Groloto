<?php

namespace App\Controller;

use App\Repository\BenevoleRepository;
use App\Repository\CreneauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BenevoleController extends AbstractController
{
    #[Route('/benevoles', name: 'benevoles')]
    public function index(BenevoleRepository $benevoleRepository, CreneauRepository $creneauRepository): Response
    {
        // Récupération des bénévoles depuis la base de données
        $benevoles = $benevoleRepository->findActiveWithUser();
        $totalBenevoles = $benevoleRepository->countActive();
        $nouveauxBenevoles = count($benevoleRepository->findRecentlyRegistered());

        // Données pour les métriques
        $metriques = [
            'total' => $totalBenevoles,
            'nouveaux' => $nouveauxBenevoles,
            'actifs' => $totalBenevoles, // Pour l'instant, tous les bénévoles récupérés sont actifs
            'disponibles' => $totalBenevoles // Placeholder pour le moment
        ];

        // Récupération des créneaux pour le planning (seulement vendredis, samedis, dimanches)
        $creneauxBruts = $creneauRepository->findWeekendSlots();
        
        // Formatage des créneaux pour FullCalendar
        $creneaux = [];
        foreach ($creneauxBruts as $creneau) {
            $creneaux[] = [
                'id' => $creneau->getId(),
                'title' => $creneau->getTitre(),
                'start' => $creneau->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $creneau->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'extendedProps' => [
                    'evenement' => $creneau->getEvenement() ? $creneau->getEvenement()->getNom() : null,
                    'poste_requis' => $creneau->getPosteRequis(),
                    'max_personnes' => $creneau->getMaxPersonnes(),
                    'remarques' => $creneau->getRemarque()
                ]
            ];
        }

        // Créneaux proches pour la sidebar
        $creneauxProches = $creneauRepository->findUpcomingWeekendSlots(5);

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevoles,
            'metriques' => $metriques,
            'creneaux' => $creneaux,
            'creneaux_proches' => $creneauxProches
        ]);
    }
}