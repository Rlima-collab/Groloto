<?php

namespace App\Controller;

use App\Entity\Weekend;
use App\Form\WeekendType;
use App\Repository\WeekendRepository;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/weekend')]
class WeekendController extends AbstractController
{
    #[Route('/', name: 'app_weekends')]
    public function index(
        WeekendRepository $weekendRepo,
        EvenementRepository $evenementRepo,
        TacheRepository $tacheRepo
    ): Response {
        $now = new \DateTime();
        $debutMois = (clone $now)->modify('first day of this month 00:00:00');
        $finMois = (clone $now)->modify('last day of this month 23:59:59');

        $evenements = $evenementRepo->findAll();
        $evenementsFuturs = array_filter($evenements, fn($e) => $e->getDateDebut() > $now);
        $evenementsPasses = array_filter($evenements, fn($e) => $e->getDateDebut() <= $now);
        $evenementsCeMois = array_filter($evenements, fn($e) => $e->getDateDebut() >= $debutMois && $e->getDateDebut() <= $finMois);

        // Formatage pour FullCalendar
        $creneaux = array_map(function ($e) {
            $end = $e->getDateFin() ? (clone $e->getDateFin())->modify('+1 day')->format('Y-m-d') : null;
            return [
                'title' => $e->getNom(),
                'start' => $e->getDateDebut()->format('Y-m-d'),
                'end' => $end,
                'type' => 'evenement',
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'extendedProps' => [
                    'description' => $e->getDescription(),
                    'lieu' => $e->getLieu(),
                ]
            ];
        }, $evenements);

        $taches = array_map(function ($t) {
            return [
                'title' => $t->getTitre() . ' (' . $t->getPosteRequis() . ')',
                'start' => $t->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $t->getFin()->format('Y-m-d\TH:i:s'),
                'type' => 'tache',
                'extendedProps' => [
                    'evenement' => $t->getEvenement()?->getNom(),
                    'poste_requis' => $t->getPosteRequis(),
                    'max_personnes' => $t->getMaxPersonnes(),
                    'notes' => $t->getRemarque(),
                ]
            ];
        }, $tacheRepo->findAll());

        return $this->render('weekend/weekends.html.twig', [
            'weekends' => $weekendRepo->findAll(),
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),

            // Statistiques
            'total_evenements' => count($evenements),
            'evenements_futurs' => $evenementsFuturs,
            'evenements_passes' => $evenementsPasses,
            'evenements_ce_mois' => count($evenementsCeMois),

            // Calendrier
            'creneaux' => json_encode($creneaux, JSON_UNESCAPED_SLASHES),
            'taches' => json_encode($taches, JSON_UNESCAPED_SLASHES),
        ]);
    }

    #[Route('/create', name: 'app_weekend_create')]
    public function create(Request $request): Response
    {
        $weekend = new Weekend();
        $form = $this->createForm(WeekendType::class, $weekend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($weekend);
            $em->flush();

            $this->addFlash('success', 'Weekend créé avec ses événements !');
            return $this->redirectToRoute('app_weekends');
        }

        return $this->render('weekend/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}