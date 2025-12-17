<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use App\Repository\WeekendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'app_evenements')]
    public function index(EvenementRepository $evenementRepo, TacheRepository $tacheRepo, CsrfTokenManagerInterface $csrfTokenManager): Response
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
                'extendedProps' => [
                    'entity' => 'tache',
                    'tacheId' => $tache->getId(),
                ],
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
                'extendedProps' => [
                    'entity' => 'evenement',
                    'evenementId' => $evenement->getId(),
                    'lieu' => $evenement->getLieu(),
                    'description' => $evenement->getDescription(),
                    'isAdmin' => $isAdmin,
                    'editUrl' => $this->generateUrl('app_evenement_edit', ['id' => $evenement->getId()]),
                    'deleteUrl' => $this->generateUrl('app_evenement_delete', ['id' => $evenement->getId()]),
                    'csrfToken' => $csrfTokenManager->getToken('delete_evenement_' . $evenement->getId())->getValue(),
                ],
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
    #[Route('/evenements/create/{preselect_weekend}', name: 'app_evenement_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ?int $preselect_weekend = null): Response
    {
        $evenement = new Evenement();

        // Définir la date par défaut à aujourd'hui
        $evenement->setDateDebut(new \DateTime());
        
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/images/evenements',
                        $newFilename
                    );
                    $evenement->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Si pas de date_fin définie, utiliser date_debut
            if (!$evenement->getDateFin() && $evenement->getDateDebut()) {
                $evenement->setDateFin($evenement->getDateDebut());
            }

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('app_evenements');
        }

        return $this->render('evenement/create.html.twig', [
            'form' => $form->createView(),
            'preselect_weekend' => $preselect_weekend,
        ]);
    }

    #[Route('/evenements/edit/{id}', name: 'app_evenement_edit')]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($evenement->getImage()) {
                    $oldImagePath = $this->getParameter('kernel.project_dir').'/public/images/evenements/'.$evenement->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/images/evenements',
                        $newFilename
                    );
                    $evenement->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Si pas de date_fin définie, utiliser date_debut
            if (!$evenement->getDateFin() && $evenement->getDateDebut()) {
                $evenement->setDateFin($evenement->getDateDebut());
            }

            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès !');
            return $this->redirectToRoute('app_evenements');
        }

        return $this->render('evenement/edit.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }

    #[Route('/evenements/liste', name: 'app_evenements_liste')]
    public function liste(Request $request, EvenementRepository $evenementRepo, WeekendRepository $weekendRepo): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        
        // Récupérer tous les weekends pour le filtre
        $weekends = $weekendRepo->findAll();
        
        // Récupérer le filtre depuis la requête
        $weekendFilter = $request->query->get('weekend', 'all');
        $statutFilter = $request->query->get('statut', 'all');
        
        // Récupérer tous les événements
        $evenements = $evenementRepo->findAll();
        
        // Filtrer par weekend si nécessaire
        if ($weekendFilter !== 'all') {
            $evenements = array_filter($evenements, function($evenement) use ($weekendFilter) {
                return $evenement->getWeekend() && $evenement->getWeekend()->getId() == $weekendFilter;
            });
        }
        
        // Filtrer par statut (futur/passé)
        $now = new \DateTime();
        if ($statutFilter === 'futurs') {
            $evenements = array_filter($evenements, function($evenement) use ($now) {
                return $evenement->getDateFin() >= $now;
            });
        } elseif ($statutFilter === 'passes') {
            $evenements = array_filter($evenements, function($evenement) use ($now) {
                return $evenement->getDateFin() < $now;
            });
        }
        
        // Trier par date (plus récents en premier pour futurs, plus anciens en premier pour passés)
        usort($evenements, function($a, $b) use ($now, $statutFilter) {
            if ($statutFilter === 'passes') {
                return $b->getDateDebut() <=> $a->getDateDebut();
            }
            return $a->getDateDebut() <=> $b->getDateDebut();
        });
        
        return $this->render('evenement/liste.html.twig', [
            'evenements' => $evenements,
            'weekends' => $weekends,
            'weekendFilter' => $weekendFilter,
            'statutFilter' => $statutFilter,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/evenements/{id}/delete', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete_evenement_' . $evenement->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_evenements_liste');
        }

        if ($evenement->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/images/evenements/' . $evenement->getImage();
            if (is_file($imagePath)) {
                @unlink($imagePath);
            }
        }

        $em->remove($evenement);
        $em->flush();

        $this->addFlash('success', 'Événement supprimé avec succès.');
        
        // Rediriger vers le calendrier ou la liste selon la provenance
        $from = $request->query->get('from');
        if ($from === 'calendar') {
            return $this->redirectToRoute('app_evenements');
        }
        return $this->redirectToRoute('app_evenements_liste');
    }
}