<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\TacheRepository;
use App\Repository\WeekendRepository;
use App\Repository\AffectationTacheRepository;
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
    public function index(WeekendRepository $weekendRepo, EvenementRepository $evenementRepo, TacheRepository $tacheRepo, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        // Récupérer les weekends à partir d'aujourd'hui (pas les passés), triés par date croissante
        $today = new \DateTime('today');
        $weekends = $weekendRepo->findBy([], ['date_debut' => 'ASC']);
        
        // Filtrer pour garder seulement les weekends futurs ou en cours
        $futureWeekends = array_filter($weekends, function($weekend) use ($today) {
            return $weekend->getDateFin() >= $today;
        });
        
        $weekendsData = [];
        $totalEvenements = 0;

        foreach ($futureWeekends as $weekend) {
            $nbEvenements = $weekend->getEvenements()->count();
            $totalEvenements += $nbEvenements;
            
            $weekendsData[] = [
                'id' => $weekend->getId(),
                'nom' => $weekend->getNom(),
                'date_debut' => $weekend->getDateDebut(),
                'date_fin' => $weekend->getDateFin(),
                'description' => $weekend->getDescription(),
                'cover_image' => $weekend->getCoverImage(),
                'nb_evenements' => $nbEvenements
            ];
        }

        $totalWeekends = count($futureWeekends);

        return $this->render('evenements.html.twig', [
            'weekends' => $weekendsData,
            'total_weekends' => $totalWeekends,
            'total_evenements' => $totalEvenements,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/evenements/weekend/{id}', name: 'app_evenements_weekend')]
    public function weekendDetail(
        int $id,
        WeekendRepository $weekendRepo,
        EntityManagerInterface $em
    ): Response {
        $weekend = $weekendRepo->find($id);
        
        if (!$weekend) {
            $this->addFlash('error', 'Weekend non trouvé.');
            return $this->redirectToRoute('app_evenements');
        }
        
        $evenements = $weekend->getEvenements()->toArray();
        
        return $this->render('evenement/weekend_detail.html.twig', [
            'weekend' => $weekend,
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenements/weekend/{id}/pdf', name: 'app_evenements_weekend_pdf')]
    public function weekendPdf(
        int $id,
        WeekendRepository $weekendRepo,
        TacheRepository $tacheRepo,
        AffectationTacheRepository $affectationRepo
    ): Response {
        $weekend = $weekendRepo->find($id);
        
        if (!$weekend) {
            $this->addFlash('error', 'Weekend non trouvé.');
            return $this->redirectToRoute('app_evenements');
        }
        
        $evenements = $weekend->getEvenements()->toArray();
        $taches = $tacheRepo->findBy(['weekend' => $weekend]);
        
        // Organiser les données par jour
        $joursFr = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
            4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
        ];
        
        $planningParJour = [];
        
        // Ajouter les événements
        foreach ($evenements as $evenement) {
            $dateDebut = $evenement->getDateDebut();
            if ($dateDebut) {
                $jour = $dateDebut->format('Y-m-d');
                $jourNom = $joursFr[(int)$dateDebut->format('N')] ?? 'Jour';
                
                if (!isset($planningParJour[$jour])) {
                    $planningParJour[$jour] = [
                        'jourNom' => $jourNom,
                        'date' => $dateDebut->format('d/m/Y'),
                        'items' => []
                    ];
                }
                
                $planningParJour[$jour]['items'][] = [
                    'type' => 'evenement',
                    'titre' => $evenement->getNom(),
                    'horaire' => $evenement->getHeureDebut() ? $evenement->getHeureDebut()->format('H\hi') : 'Horaire non défini',
                    'lieu' => $evenement->getLieu(),
                    'description' => $evenement->getDescription(),
                    'benevoles' => []
                ];
            }
        }
        
        // Ajouter les tâches avec leurs bénévoles
        foreach ($taches as $tache) {
            $affectations = $affectationRepo->findBy(['tache' => $tache]);
            $benevoles = [];
            
            // Calculer l'horaire de la tâche
            $tacheDebut = $tache->getDebut();
            $tacheFin = $tache->getFin();
            $horaireDebutStr = $tacheDebut ? $tacheDebut->format('H\hi') : '';
            $horaireFinStr = $tacheFin ? $tacheFin->format('H\hi') : '';
            $horaireTache = $horaireDebutStr . ($horaireFinStr ? ' - ' . $horaireFinStr : '');
            
            foreach ($affectations as $affectation) {
                $benevole = $affectation->getBenevole();
                if ($benevole && $benevole->getUtilisateur()) {
                    $user = $benevole->getUtilisateur();
                    $benevoles[] = [
                        'nom' => $user->getPrenom() . ' ' . $user->getNom(),
                        'statut' => $affectation->getStatut(),
                        'horaire' => $horaireTache
                    ];
                }
            }
            
            $dateDebut = $tache->getDebut();
            if ($dateDebut) {
                $jour = $dateDebut->format('Y-m-d');
                $jourNom = $joursFr[(int)$dateDebut->format('N')] ?? 'Jour';
                
                if (!isset($planningParJour[$jour])) {
                    $planningParJour[$jour] = [
                        'jourNom' => $jourNom,
                        'date' => $dateDebut->format('d/m/Y'),
                        'items' => []
                    ];
                }
                
                $horaireFin = $tache->getFin() ? $tache->getFin()->format('H\hi') : '';
                $horaireDebut = $dateDebut->format('H\hi');
                $horaire = $horaireDebut . ($horaireFin ? ' - ' . $horaireFin : '');
                
                $planningParJour[$jour]['items'][] = [
                    'type' => 'tache',
                    'titre' => $tache->getTitre(),
                    'horaire' => $horaire,
                    'lieu' => $tache->getPosteRequis(),
                    'description' => $tache->getRemarque(),
                    'benevoles' => $benevoles,
                    'maxPersonnes' => $tache->getMaxPersonnes()
                ];
            }
        }
        
        // Trier par jour
        ksort($planningParJour);
        
        // Trier les items de chaque jour par horaire
        foreach ($planningParJour as &$dayData) {
            usort($dayData['items'], function($a, $b) {
                return strcmp($a['horaire'], $b['horaire']);
            });
        }
        
        // Générer le HTML pour le PDF
        $html = $this->renderView('evenement/weekend_pdf.html.twig', [
            'weekend' => $weekend,
            'evenements' => $evenements,
            'planningParJour' => $planningParJour,
        ]);
        
        // Créer le PDF avec Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $filename = sprintf(
            'evenements_%s_%s.pdf',
            str_replace(' ', '_', $weekend->getNom()),
            $weekend->getDateDebut()->format('d-m-Y')
        );
        
        // Retourner le PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/evenements/create', name: 'app_evenements_create')]
    #[Route('/evenements/create/{preselect_weekend}', name: 'app_evenement_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ?int $preselect_weekend = null): Response
    {
        $evenement = new Evenement();
        
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation : vérifier que la date de l'événement est dans la période du weekend
            $weekend = $evenement->getWeekend();
            $dateEvenement = $evenement->getDateDebut();
            
            if ($weekend && $dateEvenement) {
                $dateDebutWeekend = $weekend->getDateDebut();
                $dateFinWeekend = $weekend->getDateFin();
                
                if ($dateEvenement < $dateDebutWeekend || $dateEvenement > $dateFinWeekend) {
                    $this->addFlash('error', 'La date de l\'événement doit être comprise entre le ' . 
                        $dateDebutWeekend->format('d/m/Y') . ' et le ' . $dateFinWeekend->format('d/m/Y') . 
                        ' (période du week-end sélectionné).');
                    return $this->render('evenement/create.html.twig', [
                        'form' => $form->createView(),
                        'preselect_weekend' => $preselect_weekend,
                    ]);
                }
            }

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
        
        // Récupérer le filtre depuis la requête (par défaut: futurs)
        $weekendFilter = $request->query->get('weekend', 'all');
        $statutFilter = $request->query->get('statut', 'futurs');
        
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