<?php

namespace App\Controller;

use App\Entity\Weekend;
use App\Form\WeekendType;
use App\Repository\WeekendRepository;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $finMois   = (clone $now)->modify('last day of this month 23:59:59');

        // ─────────────── ÉVÉNEMENTS ───────────────
        $evenements = $evenementRepo->findAll();

        $evenementsFuturs = array_filter($evenements, fn($e) => $e->getDateDebut() > $now);
        $evenementsPasses = array_filter($evenements, fn($e) => $e->getDateDebut() <= $now);
        $evenementsCeMois = array_filter(
            $evenements,
            fn($e) => $e->getDateDebut() >= $debutMois && $e->getDateDebut() <= $finMois
        );

        // FullCalendar : événements
        $creneaux = array_map(function ($e) {
            $end = $e->getDateFin()
                ? (clone $e->getDateFin())->modify('+1 day')->format('Y-m-d')
                : null;

            return [
                'title'           => $e->getNom(),
                'start'           => $e->getDateDebut()->format('Y-m-d'),
                'end'             => $end,
                'type'            => 'evenement',
                'backgroundColor' => '#3b82f6',
                'borderColor'     => '#2563eb',
                'extendedProps'   => [
                    'description' => $e->getDescription() ?? '',
                    'lieu'        => $e->getLieu() ?? '',
                ],
            ];
        }, $evenements);

        // Weekends pour le calendrier
        $weekendsData = array_map(function ($w) {
            $end = $w->getDateDimanche() ? (clone $w->getDateDimanche())->modify('+1 day')->format('Y-m-d') : null;
            $evenementsNoms = array_map(fn($e) => $e->getNom(), $w->getEvenements()->toArray());
            return [
                'title' => 'Weekend du ' . $w->getDateVendredi()->format('d/m'),
                'start' => $w->getDateVendredi()->format('Y-m-d'),
                'end' => $end,
                'type' => 'weekend',
                'backgroundColor' => '#10b981',
                'borderColor' => '#059669',
                'extendedProps' => [
                    'evenements' => implode(', ', $evenementsNoms),
                ]
            ];
        }, $weekendRepo->findAll());

        $tachesBrutes = $tacheRepo->findAll();

        $taches = array_map(function ($t) {
            $weekend = $t->getWeekend();

            $nomWeekend   = $weekend?->getNom()               ?? 'Weekend non défini';
            $datesWeekend = $weekend
                ? $weekend->getDateVendredi()->format('d/m') . ' → ' . $weekend->getDateDimanche()->format('d/m/Y')
                : 'Dates inconnues';

            return [
                'title'           => $t->getTitre() . ' (' . ($t->getPosteRequis() ?? 'aucun poste') . ')',
                'start'           => $t->getDebut()->format('Y-m-d\TH:i:s'),
                'end'             => $t->getFin()->format('Y-m-d\TH:i:s'),
                'type'            => 'tache',
                'backgroundColor' => '#10b981',
                'borderColor'     => '#059669',
                'extendedProps'   => [
                    'weekend'        => $nomWeekend,
                    'weekend_dates'  => $datesWeekend,
                    'poste_requis'   => $t->getPosteRequis() ?? 'Non défini',
                    'max_personnes'  => $t->getMaxPersonnes() ?? 'Illimité',
                    'notes'          => $t->getRemarque() ?? '',
                ],
            ];
        }, $tachesBrutes);

        // ─────────────── WEEKENDS POUR JS ───────────────
        $weekendsForJs = array_map(function($w) {
            return [
                'id' => $w->getId(),
                'dateVendredi' => $w->getDateVendredi()->format('Y-m-d'),
                'dateSamedi' => $w->getDateSamedi()->format('Y-m-d'),
                'dateDimanche' => $w->getDateDimanche()->format('Y-m-d'),
                'coverImage' => $w->getCoverImage(),
                'evenements' => array_map(fn($e) => [
                    'id' => $e->getId(),
                    'nom' => $e->getNom(),
                    'dateDebut' => $e->getDateDebut()->format('Y-m-d'),
                    'dateFin' => $e->getDateFin() ? $e->getDateFin()->format('Y-m-d') : null,
                    'lieu' => $e->getLieu(),
                    'description' => $e->getDescription(),
                ], $w->getEvenements()->toArray()),
                'taches' => array_map(fn($t) => [
                    'id' => $t->getId(),
                    'titre' => $t->getTitre(),
                    'debut' => $t->getDebut()->format('Y-m-d\TH:i:s'),
                    'fin' => $t->getFin()->format('Y-m-d\TH:i:s'),
                    'posteRequis' => $t->getPosteRequis(),
                    'maxPersonnes' => $t->getMaxPersonnes(),
                ], $w->getTaches()->toArray()),
            ];
        }, $weekendRepo->findAll());

        // ─────────────── RENDER ───────────────
        return $this->render('weekend/weekends.html.twig', [
            'weekends'           => $weekendRepo->findAll(),
            'isAdmin'            => $this->isGranted('ROLE_ADMIN'),

            // Stats
            'total_evenements'   => count($evenements),
            'evenements_futurs'  => count($evenementsFuturs),
            'evenements_passes'  => count($evenementsPasses),
            'evenements_ce_mois' => count($evenementsCeMois),

            // Calendrier
            'creneaux' => json_encode($creneaux, JSON_UNESCAPED_SLASHES),
            'weekendsData' => json_encode($weekendsData, JSON_UNESCAPED_SLASHES),
            'weekendsForJs' => json_encode($weekendsForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'taches' => json_encode($taches, JSON_UNESCAPED_SLASHES),
        ]);
    }

    #[Route('/create', name: 'app_weekend_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $weekend = new Weekend();
        $form    = $this->createForm(WeekendType::class, $weekend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le nombre de jours du formulaire
            $nombreJours = $form->get('nombre_jours')->getData() ?? 3;
            
            // Calculer automatiquement samedi et dimanche à partir du vendredi
            $dateVendredi = $weekend->getDateVendredi();
            if ($dateVendredi) {
                // Samedi = vendredi + 1 jour
                $dateSamedi = (clone $dateVendredi)->modify('+1 day');
                $weekend->setDateSamedi($dateSamedi);
                
                // Dimanche = vendredi + 2 jours (ou selon le nombre de jours)
                if ($nombreJours >= 3) {
                    $dateDimanche = (clone $dateVendredi)->modify('+2 days');
                    $weekend->setDateDimanche($dateDimanche);
                }
            }
            
            // Gestion de l'upload de l'image de couverture (optionnel)
            /** @var UploadedFile|null $coverFile */
            $coverFile = $form->get('cover_image')->getData();
            if ($coverFile) {
                $projectDir = $this->getParameter('kernel.project_dir');
                $uploadsDir = $projectDir . '/public/uploads/weekends';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0755, true);
                }

                $originalExtension = $coverFile->guessExtension() ?: 'jpg';
                $safeName = uniqid('weekend_') . '.' . $originalExtension;
                try {
                    $coverFile->move($uploadsDir, $safeName);
                    // stocker le chemin relatif
                    $weekend->setCoverImage('uploads/weekends/' . $safeName);
                } catch (FileException $e) {
                    // Ne pas bloquer la création du weekend en cas d'erreur d'upload
                    $this->addFlash('warning', 'Impossible d\'uploader l\'image de couverture. Le weekend a été créé sans image.');
                }
            }

            $em->persist($weekend);
            $em->flush();

            $this->addFlash('success', 'Weekend créé avec succès !');
            return $this->redirectToRoute('app_weekends');
        }

        return $this->render('weekend/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}