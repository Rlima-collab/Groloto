<?php

namespace App\Controller;

use App\Entity\Weekend;
use App\Form\WeekendType;
use App\Repository\WeekendRepository;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use App\Repository\BenevoleRepository;
use App\Repository\AffectationTacheRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            $end = $w->getDateFin() ? (clone $w->getDateFin())->modify('+1 day')->format('Y-m-d') : null;
            $evenementsNoms = array_map(fn($e) => $e->getNom(), $w->getEvenements()->toArray());
            return [
                'title' => $w->getNom(),
                'start' => $w->getDateDebut()->format('Y-m-d'),
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
                ? $weekend->getDateDebut()->format('d/m') . ' → ' . $weekend->getDateFin()->format('d/m/Y')
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
                'nom' => $w->getNom(),
                'dateDebut' => $w->getDateDebut()->format('Y-m-d'),
                'dateFin' => $w->getDateFin()->format('Y-m-d'),
                'coverImage' => $w->getCoverImage(),
                'description' => $w->getDescription(),
                'evenements' => array_map(fn($e) => [
                    'id' => $e->getId(),
                    'nom' => $e->getNom(),
                    'dateDebut' => $e->getDateDebut()->format('Y-m-d'),
                    'dateFin' => $e->getDateFin() ? $e->getDateFin()->format('Y-m-d') : null,
                    'heureDebut' => $e->getHeureDebut() ? $e->getHeureDebut()->format('H:i:s') : null,
                    'dureeMinutes' => $e->getDureeMinutes(),
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
                    'nbAssignes' => count($t->getAffectations()),
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
    public function create(
        Request $request, 
        EntityManagerInterface $em,
        BenevoleRepository $benevoleRepo,
        NotificationService $notificationService
    ): Response
    {
        $weekend = new Weekend();
        $form    = $this->createForm(WeekendType::class, $weekend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $dateDebut = $weekend->getDateVendredi(); // Date de début
            $dateFin = $weekend->getDateDimanche();   // Date de fin
            
            // Validation : minimum 2 jours
            if ($dateDebut && $dateFin) {
                $interval = $dateDebut->diff($dateFin);
                $nombreJours = $interval->days + 1; // +1 pour inclure le jour de fin
                
                if ($nombreJours < 2) {
                    $this->addFlash('error', 'Le weekend doit durer au minimum 2 jours.');
                    return $this->render('weekend/create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                
                if ($dateFin < $dateDebut) {
                    $this->addFlash('error', 'La date de fin doit être après la date de début.');
                    return $this->render('weekend/create.html.twig', [
                        'form' => $form->createView(),
                    ]);
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

            // Envoyer une notification à tous les bénévoles actifs
            $benevoles = $benevoleRepo->findBy(['actif' => true]);
            $dateDebutStr = $weekend->getDateDebut()->format('d/m/Y');
            $dateFinStr = $weekend->getDateFin()->format('d/m/Y');
            
            foreach ($benevoles as $benevole) {
                if ($benevole->getUtilisateur()) {
                    $notificationService->createNotification(
                        $benevole->getUtilisateur(),
                        'nouveau_weekend',
                        "🎉 Nouveau weekend \"{$weekend->getNom()}\" du {$dateDebutStr} au {$dateFinStr} ! Indiquez vos disponibilités.",
                        '/benevole/disponibilites/weekend/' . $weekend->getId()
                    );
                }
            }

            // Stocker l'ID du weekend dans la session pour le pré-sélectionner
            $request->getSession()->set('last_created_weekend_id', $weekend->getId());

            $this->addFlash('success', 'Weekend créé avec succès ! Les bénévoles ont été notifiés.');
            
            // Rediriger vers une page de confirmation qui propose d'ajouter un événement
            return $this->redirectToRoute('app_weekend_created', ['id' => $weekend->getId()]);
        }

        return $this->render('weekend/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/created/{id}', name: 'app_weekend_created')]
    public function created(Weekend $weekend): Response
    {
        return $this->render('weekend/created.html.twig', [
            'weekend' => $weekend,
        ]);
    }

    #[Route('/liste', name: 'app_weekend_liste')]
    public function liste(WeekendRepository $weekendRepo): Response
    {
        return $this->render('weekend/liste.html.twig', [
            'weekends' => $weekendRepo->findBy([], ['date_debut' => 'DESC']),
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
        ]);
    }

    #[Route('/{id}/details', name: 'app_weekend_details')]
    public function details(Weekend $weekend, \App\Repository\BenevoleRepository $benevoleRepo): Response
    {
        $benevoles = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $benevoles = $benevoleRepo->findByWeekend($weekend->getId());
        }

        return $this->render('weekend/details.html.twig', [
            'weekend' => $weekend,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
            'benevoles' => $benevoles,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_weekend_edit')]
    public function edit(Request $request, Weekend $weekend, EntityManagerInterface $em): Response
    {
        // Vérifier que l'utilisateur est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(WeekendType::class, $weekend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de la nouvelle image de couverture
            /** @var UploadedFile|null $coverFile */
            $coverFile = $form->get('cover_image')->getData();
            if ($coverFile) {
                $projectDir = $this->getParameter('kernel.project_dir');
                $uploadsDir = $projectDir . '/public/uploads/weekends';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0755, true);
                }

                // Supprimer l'ancienne image si elle existe
                if ($weekend->getCoverImage()) {
                    $oldImagePath = $projectDir . '/public/' . $weekend->getCoverImage();
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }

                $originalExtension = $coverFile->guessExtension() ?: 'jpg';
                $safeName = uniqid('weekend_') . '.' . $originalExtension;
                try {
                    $coverFile->move($uploadsDir, $safeName);
                    $weekend->setCoverImage('uploads/weekends/' . $safeName);
                } catch (FileException $e) {
                    $this->addFlash('warning', 'Impossible de modifier l\'image de couverture.');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Weekend modifié avec succès !');
            return $this->redirectToRoute('app_weekend_liste');
        }

        return $this->render('weekend/edit.html.twig', [
            'form' => $form->createView(),
            'weekend' => $weekend,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_weekend_delete', methods: ['POST'])]
    public function delete(Request $request, Weekend $weekend, EntityManagerInterface $em): Response
    {
        // Vérifier que l'utilisateur est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $weekend->getId(), $request->request->get('_token'))) {
            // Supprimer l'image de couverture si elle existe
            if ($weekend->getCoverImage()) {
                $projectDir = $this->getParameter('kernel.project_dir');
                $imagePath = $projectDir . '/public/' . $weekend->getCoverImage();
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            $em->remove($weekend);
            $em->flush();

            $this->addFlash('success', 'Weekend supprimé avec succès !');
        }

        return $this->redirectToRoute('app_weekend_liste');
    }

    #[Route('/{id}/planning/export-pdf', name: 'app_weekend_planning_pdf')]
    public function exportPlanningPdf(
        Weekend $weekend,
        TacheRepository $tacheRepository,
        AffectationTacheRepository $affectationRepository
    ): Response {
        // Récupérer toutes les tâches du weekend
        $taches = $tacheRepository->findBy(['weekend' => $weekend]);
        
        $joursFr = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 
            4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
        ];
        
        // Générer les créneaux de 30 minutes de 8h00 à 00h30
        $timeSlots = [];
        for ($h = 8; $h <= 24; $h++) {
            for ($m = 0; $m < 60; $m += 30) {
                if ($h == 24 && $m > 0) break;
                $currentTime = sprintf('%02d:%02d', $h % 24, $m);
                $timeSlots[] = [
                    'key' => $currentTime,
                    'label' => sprintf('%02d:%02d - %02d%s', $h % 24, $m, ($m == 30 ? ($h+1) % 24 : $h % 24), ($m == 30 ? 'h' : 'h30'))
                ];
            }
        }
        
        // Collecter toutes les assignations de bénévoles
        $allAssignments = [];
        $planningData = [];
        
        foreach ($taches as $tache) {
            // Récupérer TOUTES les affectations (assigne ET confirme)
            $affectations = $affectationRepository->findBy(['tache' => $tache]);
            
            // Déterminer les horaires de la tâche
            $plagesHoraires = $tache->getPlagesHoraires();
            
            $horaires = [];
            if (!$plagesHoraires->isEmpty()) {
                foreach ($plagesHoraires as $plage) {
                    $horaires[] = [
                        'jour' => $plage->getJour()->format('Y-m-d'),
                        'jourNom' => $joursFr[(int)$plage->getJour()->format('N')] ?? 'Jour',
                        'debut' => $plage->getHeureDebut()->format('H:i'),
                        'fin' => $plage->getHeureFin()->format('H:i')
                    ];
                }
            } elseif ($tache->getDebut()) {
                $horaires[] = [
                    'jour' => $tache->getDebut()->format('Y-m-d'),
                    'jourNom' => $joursFr[(int)$tache->getDebut()->format('N')] ?? 'Jour',
                    'debut' => $tache->getDebut()->format('H:i'),
                    'fin' => $tache->getFin() ? $tache->getFin()->format('H:i') : $tache->getDebut()->format('H:i')
                ];
            }
            
            // Pour chaque bénévole affecté
            foreach ($affectations as $affectation) {
                $benevole = $affectation->getBenevole();
                if (!$benevole || !$benevole->getUtilisateur()) continue;
                
                $prenom = $benevole->getUtilisateur()->getPrenom();
                
                foreach ($horaires as $horaire) {
                    $jour = $horaire['jour'];
                    
                    if (!isset($planningData[$jour])) {
                        $planningData[$jour] = [
                            'jourNom' => $horaire['jourNom'],
                            'assignments' => []
                        ];
                    }
                    
                    $allAssignments[] = [
                        'jour' => $jour,
                        'nom' => $prenom,
                        'debut' => $horaire['debut'],
                        'fin' => $horaire['fin'],
                        'heuresLabel' => $this->formatHeure($horaire['debut']) . '-' . $this->formatHeure($horaire['fin'])
                    ];
                }
            }
        }
        
        // Trier les jours
        ksort($planningData);
        
        // Organiser les assignations dans la grille par jour
        foreach ($planningData as $jour => &$dayData) {
            $dayAssignments = array_filter($allAssignments, fn($a) => $a['jour'] === $jour);
            $dayData['assignments'] = array_values($dayAssignments);
            $dayData['maxCols'] = max(3, count($dayAssignments));
        }
        
        // Générer le HTML du PDF
        $html = $this->renderView('weekend/planning_taches_pdf.html.twig', [
            'weekend' => $weekend,
            'planningData' => $planningData,
            'timeSlots' => $timeSlots,
            'allAssignments' => $allAssignments
        ]);
        
        // Créer le PDF avec Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $filename = 'planning_taches_' . $weekend->getNom() . '_' . date('Y-m-d') . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
        
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
    
    /**
     * Formate une heure HH:MM en format lisible (ex: 18h, 18h30)
     */
    private function formatHeure(string $heure): string
    {
        $parts = explode(':', $heure);
        $h = intval($parts[0]);
        $m = intval($parts[1] ?? 0);
        
        if ($m === 0) {
            return $h . 'h';
        }
        return $h . 'h' . sprintf('%02d', $m);
    }
    
    /**
     * Vérifie si un temps est dans une plage horaire
     */
    private function isTimeInRange(string $time, string $start, string $end): bool
    {
        $timeMinutes = $this->timeToMinutes($time);
        $startMinutes = $this->timeToMinutes($start);
        $endMinutes = $this->timeToMinutes($end);
        
        // Gérer le cas où la fin est après minuit
        if ($endMinutes < $startMinutes) {
            $endMinutes += 24 * 60;
            if ($timeMinutes < $startMinutes) {
                $timeMinutes += 24 * 60;
            }
        }
        
        return $timeMinutes >= $startMinutes && $timeMinutes < $endMinutes;
    }
    
    /**
     * Convertit une heure HH:MM en minutes
     */
    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return intval($parts[0]) * 60 + intval($parts[1] ?? 0);
    }
}