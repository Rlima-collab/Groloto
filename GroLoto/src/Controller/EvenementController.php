<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'app_evenements')]
    public function index(EvenementRepository $evenementRepo, TacheRepository $tacheRepo): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $now = new \DateTime();

        $evenements = $evenementRepo->findAll();
        $evenementsPasses = [];
        $evenementsFuturs = [];
        $evenementsCeMois = [];

        $debutMois = new \DateTime('first day of this month 00:00:00');
        $finMois = new \DateTime('last day of this month 23:59:59');

        foreach ($evenements as $evenement) {
            if ($evenement->getDateFin() < $now) {
                $evenementsPasses[] = $evenement;
            } else {
                $evenementsFuturs[] = $evenement;
            }
            if ($evenement->getDateDebut() >= $debutMois && $evenement->getDateDebut() <= $finMois) {
                $evenementsCeMois[] = $evenement;
            }
        }

        usort($evenementsPasses, fn($a, $b) => $b->getDateFin() <=> $a->getDateFin());
        usort($evenementsFuturs, fn($a, $b) => $a->getDateDebut() <=> $b->getDateDebut());

        $taches = $tacheRepo->findAll();
        $calendrierData = [];

        foreach ($taches as $tache) {
            $calendrierData[] = [
                'id' => 'tache_' . $tache->getId(),
                'title' => $tache->getTitre() ?: 'Tâche',
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#3b82f6',
                'className' => 'tache-event',
            ];
        }

        foreach ($evenements as $evenement) {
            $isPasse = $evenement->getDateFin() < $now;
            $start = $evenement->getDateDebut()?->format('Y-m-d') . 'T09:00:00';
            $end = $evenement->getDateFin()?->format('Y-m-d') . 'T18:00:00';

            $calendrierData[] = [
                'id' => 'evenement_' . $evenement->getId(),
                'title' => $evenement->getNom(),
                'start' => $start,
                'end' => $end,
                'backgroundColor' => $isPasse ? '#6b7280' : '#10b981',
                'borderColor' => $isPasse ? '#6b7280' : '#10b981',
                'className' => 'evenement-event',
            ];
        }

        return $this->render('evenements.html.twig', [
            'evenements_passes' => $evenementsPasses,
            'evenements_futurs' => $evenementsFuturs,
            'evenements_ce_mois' => count($evenementsCeMois),
            'taches' => json_encode($calendrierData),
            'metriques' => [
                'total' => count($evenements),
                'futurs' => count($evenementsFuturs),
                'passes' => count($evenementsPasses),
                'taches_total' => count($taches)
            ],
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

            // Vendredi → date_debut
            $evenement->setDateDebut($dateVendredi);

            // Dimanche = vendredi + 2 jours
            $dateFin = clone $dateVendredi;
            $dateFin->modify('+2 days');
            $evenement->setDateFin($dateFin);

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement créé : du ' . $dateVendredi->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'));
            return $this->redirectToRoute('app_evenements');
        }

        return $this->render('evenement/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}