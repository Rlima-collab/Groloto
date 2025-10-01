<?php

namespace App\Controller;

use App\Repository\BenevoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BenevoleController extends AbstractController
{
    #[Route('/benevoles', name: 'benevoles')]
    public function index(BenevoleRepository $benevoleRepository): Response
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

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevoles,
            'metriques' => $metriques
        ]);
    }
}