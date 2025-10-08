<?php
namespace App\Controller;
use App\Repository\MeceneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MeceneController extends AbstractController
{
    #[Route('/mecenes', name: 'mecenes')]
    public function index(MeceneRepository $meceneRepository): Response
    {
        // Récupération des mécènes depuis la base de données
        $mecenes = $meceneRepository->findAllWithUser();
        $totalMecenes = $meceneRepository->countAll();
        $nouveauxMecenes = count($meceneRepository->findRecentlyRegistered());

        // Données pour les métriques
        $metriques = [
            'total' => $totalMecenes,
            'nouveaux' => $nouveauxMecenes,
            'actifs' => $totalMecenes, // Placeholder for now
            'disponibles' => $totalMecenes // Placeholder for now
        ];

        return $this->render('mecenes.html.twig', [
            'mecenes' => $mecenes,
            'metriques' => $metriques
        ]);
    }
}