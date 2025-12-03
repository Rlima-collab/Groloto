<?php

namespace App\Controller;

use App\Entity\Benevole;
use App\Form\BenevoleEditType;
use App\Repository\BenevoleRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BenevoleController extends AbstractController
{
    #[Route('/benevoles', name: 'benevoles')]
    public function index(BenevoleRepository $benevoleRepository, TacheRepository $tacheRepository): Response
    {
        // Récupération des bénévoles
        $benevoles = $benevoleRepository->findActiveWithUser();
        
        // Ajouter le nombre de tâches réalisées pour chaque bénévole
        $benevolesAvecStats = [];
        foreach ($benevoles as $benevole) {
            $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());
            $benevolesAvecStats[] = [
                'benevole' => $benevole,
                'nb_taches_realisees' => count($tachesRealisees)
            ];
        }
        
        $totalBenevoles = $benevoleRepository->countActive();
        $nouveauxBenevoles = count($benevoleRepository->findRecentlyRegistered());

        $metriques = [
            'total' => $totalBenevoles,
            'nouveaux' => $nouveauxBenevoles,
            'actifs' => $totalBenevoles,
            'disponibles' => $totalBenevoles
        ];

        // Par défaut, on récupère les tâches futures globales
        $tachesBrutes = $tacheRepository->findTachesFutures();

        // Si l'utilisateur connecté est lié à un bénévole, on filtre pour ne récupérer
        // que ses tâches assignées via AffectationTache
        $user = $this->getUser();
        $benevoleForUser = null;
        if ($user) {
            // Rechercher un objet Benevole lié à cet utilisateur via le repository injecté
            $benevoleForUser = $benevoleRepository->findOneBy(['utilisateur' => $user]);
        }

        if ($benevoleForUser) {
            $tachesBrutes = $tacheRepository->findTachesFuturesForBenevole($benevoleForUser->getId());
            // Si aucune tâche future (p.ex. tâches en 2024), on renvoie toutes les tâches assignées
            if (empty($tachesBrutes)) {
                $tachesBrutes = $tacheRepository->findTachesForBenevole($benevoleForUser->getId());
            }
        }

        $taches = [];
        foreach ($tachesBrutes as $tache) {
            $taches[] = [
                'id' => $tache->getId(),
                'title' => $tache->getTitre(),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'extendedProps' => [
                    'weekend' => $tache->getWeekend()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Pour la liste "taches_proches" à droite, on applique le même filtrage
        if ($benevoleForUser) {
            $tachesProches = $tacheRepository->findTachesFuturesForBenevole($benevoleForUser->getId());
            if (empty($tachesProches)) {
                $tachesProches = $tacheRepository->findTachesForBenevole($benevoleForUser->getId());
            }
        } else {
            $tachesProches = $tacheRepository->findTachesFutures();
        }

        // Convertir les entités Tache en tableaux simples pour le template
        $tachesProchesData = [];
        foreach ($tachesProches as $tp) {
            $tachesProchesData[] = [
                'id' => $tp->getId(),
                'titre' => $tp->getTitre(),
                'debut' => $tp->getDebut(),
                'poste_requis' => $tp->getPosteRequis(),
            ];
        }

        // Récupérer les tâches réalisées si l'utilisateur est un bénévole
        $tachesRealiseesData = [];
        if ($benevoleForUser) {
            $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevoleForUser->getId());
            foreach ($tachesRealisees as $tr) {
                $tachesRealiseesData[] = [
                    'id' => $tr->getId(),
                    'titre' => $tr->getTitre(),
                    'debut' => $tr->getDebut(),
                    'fin' => $tr->getFin(),
                    'poste_requis' => $tr->getPosteRequis(),
                ];
            }
        }

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevolesAvecStats,
            'metriques' => $metriques,
            'taches' => $taches,
            // fournir des tableaux simples pour le template
            'taches_proches' => $tachesProchesData,
            'taches_realisees' => $tachesRealiseesData
        ]);
    }

    #[Route('/benevole/mon-planning', name: 'benevole_mon_planning')]
    public function monPlanning(
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole pour accéder à cette page.');
            return $this->redirectToRoute('benevoles');
        }

        // Récupérer tous les weekends où le bénévole a des tâches
        $weekends = $tacheRepository->findWeekendsForBenevole($benevole->getId());
        
        // Calculer les statistiques
        $totalWeekends = count($weekends);
        $tachesFutures = $tacheRepository->findTachesFuturesForBenevole($benevole->getId());
        $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());
        
        return $this->render('benevoles/mon_planning.html.twig', [
            'benevole' => $benevole,
            'weekends' => $weekends,
            'total_weekends' => $totalWeekends,
            'total_taches_futures' => count($tachesFutures),
            'total_taches_realisees' => count($tachesRealisees),
        ]);
    }

    #[Route('/benevole/mon-planning/weekend/{id}', name: 'benevole_planning_weekend')]
    public function planningWeekend(
        int $id,
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole pour accéder à cette page.');
            return $this->redirectToRoute('benevoles');
        }

        // Récupérer le weekend
        $weekend = $em->getRepository(\App\Entity\Weekend::class)->find($id);
        if (!$weekend) {
            $this->addFlash('error', 'Weekend non trouvé.');
            return $this->redirectToRoute('benevole_mon_planning');
        }

        // Récupérer les tâches du bénévole pour ce weekend
        $taches = $tacheRepository->findTachesForBenevoleByWeekend($benevole->getId(), $id);
        
        if (empty($taches)) {
            $this->addFlash('warning', 'Vous n\'avez pas de tâches pour ce weekend.');
            return $this->redirectToRoute('benevole_mon_planning');
        }

        return $this->render('benevoles/planning_weekend_detail.html.twig', [
            'benevole' => $benevole,
            'weekend' => $weekend,
            'taches' => $taches,
        ]);
    }

    #[Route('/benevole/mon-planning/weekend/{id}/pdf', name: 'benevole_planning_weekend_pdf')]
    public function planningWeekendPdf(
        int $id,
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepository->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être un bénévole pour accéder à cette page.');
            return $this->redirectToRoute('benevoles');
        }

        // Récupérer le weekend
        $weekend = $em->getRepository(\App\Entity\Weekend::class)->find($id);
        if (!$weekend) {
            $this->addFlash('error', 'Weekend non trouvé.');
            return $this->redirectToRoute('benevole_mon_planning');
        }

        // Récupérer les tâches du bénévole pour ce weekend
        $taches = $tacheRepository->findTachesForBenevoleByWeekend($benevole->getId(), $id);
        
        if (empty($taches)) {
            $this->addFlash('warning', 'Vous n\'avez pas de tâches pour ce weekend.');
            return $this->redirectToRoute('benevole_mon_planning');
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('benevoles/planning_weekend_pdf.html.twig', [
            'benevole' => $benevole,
            'weekend' => $weekend,
            'taches' => $taches,
        ]);

        // Créer le PDF avec Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le nom du fichier
        $filename = sprintf(
            'planning_%s_%s.pdf',
            str_replace(' ', '_', $weekend->getNom()),
            $weekend->getDateDebut()->format('Y-m-d')
        );

        // Retourner le PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/benevoles/{id}/edit', name: 'benevole_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $utilisateur = $benevole->getUtilisateur();

        $form = $this->createForm(BenevoleEditType::class, $benevole);
        
        // Pré-remplir les champs utilisateur
        $form->get('prenom')->setData($utilisateur->getPrenom());
        $form->get('nom')->setData($utilisateur->getNom());
        $form->get('email')->setData($utilisateur->getEmail());
        $form->get('telephone')->setData($utilisateur->getTelephone());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPrenom($form->get('prenom')->getData());
            $utilisateur->setNom($form->get('nom')->getData());
            $utilisateur->setEmail($form->get('email')->getData());
            $utilisateur->setTelephone($form->get('telephone')->getData());
            $utilisateur->setDateModification(new \DateTime());

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le bénévole a été mis à jour avec succès !');
                return $this->redirectToRoute('benevoles');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du bénévole.');
            }
        }

        return $this->render('benevoles/edit.html.twig', [
            'form' => $form->createView(),
            'benevole' => $benevole,
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/benevoles/{id}/delete', name: 'benevole_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        Request $request,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_benevole_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('benevoles');
        }

        try {
            $utilisateur = $benevole->getUtilisateur();
            $nomComplet = $utilisateur->getPrenom() . ' ' . $utilisateur->getNom();
            
            $entityManager->remove($benevole);
            $entityManager->flush();
            
            $this->addFlash('success', "Le bénévole {$nomComplet} a été supprimé avec succès.");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du bénévole.');
        }

        return $this->redirectToRoute('benevoles');
    }

    #[Route('/benevoles/{id}/planning', name: 'benevole_planning')]
    #[IsGranted('ROLE_ADMIN')]
    public function planning(
        int $id,
        BenevoleRepository $benevoleRepository,
        TacheRepository $tacheRepository
    ): Response {
        $benevole = $benevoleRepository->find($id);

        if (!$benevole) {
            $this->addFlash('error', 'Bénévole non trouvé.');
            return $this->redirectToRoute('benevoles');
        }

        $utilisateur = $benevole->getUtilisateur();

        // Récupérer les tâches du bénévole
        $tachesBrutes = $tacheRepository->findTachesForBenevole($benevole->getId());

        $taches = [];
        foreach ($tachesBrutes as $tache) {
            $taches[] = [
                'id' => $tache->getId(),
                'title' => $tache->getTitre(),
                'start' => $tache->getDebut()->format('Y-m-d\TH:i:s'),
                'end' => $tache->getFin()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#0d1b2a',
                'borderColor' => '#1a3c5a',
                'extendedProps' => [
                    'weekend' => $tache->getWeekend()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Tâches futures
        $tachesFutures = $tacheRepository->findTachesFuturesForBenevole($benevole->getId());
        
        // Tâches réalisées
        $tachesRealisees = $tacheRepository->findTachesRealiseesForBenevole($benevole->getId());

        return $this->render('benevoles/planning.html.twig', [
            'benevole' => $benevole,
            'utilisateur' => $utilisateur,
            'taches' => json_encode($taches),
            'taches_futures' => $tachesFutures,
            'taches_realisees' => $tachesRealisees,
            'total_taches' => count($tachesBrutes),
            'total_futures' => count($tachesFutures),
            'total_realisees' => count($tachesRealisees)
        ]);
    }
}