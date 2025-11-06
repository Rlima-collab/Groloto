<?php
namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PlanningController extends AbstractController
{
    private $evenementRepository;
    private $tacheRepository;
    private $entityManager;

    public function __construct(
        EvenementRepository $evenementRepository, 
        TacheRepository $tacheRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->evenementRepository = $evenementRepository;
        $this->tacheRepository = $tacheRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/planning/events', name: 'planning_events', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        try {
            $evenements = $this->evenementRepository->findAll();
            $taches = $this->tacheRepository->findAll();
            
            $events = [];
            
            foreach ($evenements as $evenement) {
                $events[] = [
                    'id' => 'event-' . $evenement->getId(),
                    'title' => $evenement->getNom(),
                    'start' => $evenement->getDateDebut() ? $evenement->getDateDebut()->format('Y-m-d') . 'T09:00:00' : null,
                    'end' => $evenement->getDateFin() ? $evenement->getDateFin()->format('Y-m-d') . 'T18:00:00' : null,
                    'backgroundColor' => '#007bff',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'evenement',
                        'description' => $evenement->getDescription(),
                        'lieu' => $evenement->getLieu()
                    ]
                ];
            }
            
            foreach ($taches as $tache) {
                $couleur = match($tache->getPosteRequis()) {
                    'accueil' => '#28a745',
                    'bar' => '#fd7e14', 
                    'cuisine' => '#dc3545',
                    'technique' => '#6c757d',
                    'autre' => '#6f42c1',
                    default => '#17a2b8'
                };
                
                $events[] = [
                    'id' => 'tache-' . $tache->getId(),
                    'title' => $tache->getTitre() . ' (' . ucfirst($tache->getPosteRequis() ?? 'Non spécifié') . ')',
                    'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                    'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $couleur,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'tache',
                        'poste' => $tache->getPosteRequis(),
                        'max_personnes' => $tache->getMaxPersonnes(),
                        'weekend' => $tache->getWeekend() ? $tache->getWeekend()->getNom() : 'Non rattaché',
                        'lieu' => null
                    ]
                ];
            }
            
            return new JsonResponse($events);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                [
                    'id' => 'test-1',
                    'title' => 'Événement de test',
                    'start' => '2024-12-20T10:00:00',
                    'end' => '2024-12-20T18:00:00',
                    'backgroundColor' => '#28a745',
                    'textColor' => '#ffffff'
                ]
            ]);
        }
    }
}