<?php
namespace App\Controller;
use App\Repository\MeceneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MECENE')]
class MeceneController extends AbstractController
{
    #[Route('/mecenes', name: 'mecenes')]
    public function index(MeceneRepository $meceneRepository): Response
    {
        // Récupération des mécènes depuis la base de données
        $mecenes = $meceneRepository->findAllWithUser();
        $totalMecenes = $meceneRepository->countAll();

        // Données pour les métriques
        $metriques = [
            'total' => $totalMecenes,
            'nouveaux' => 0, // Placeholder: pas de date de création dans la base
            'actifs' => $totalMecenes, // Placeholder for now
            'disponibles' => $totalMecenes // Placeholder for now
        ];

        return $this->render('mecenes.html.twig', [
            'mecenes' => $mecenes,
            'metriques' => $metriques
        ]);
    }
}