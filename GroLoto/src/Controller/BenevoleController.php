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
        $benevoles = $benevoleRepository->findActiveWithUser();
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
                    'evenement' => $tache->getEvenement() ? $tache->getEvenement()->getNom() : null,
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

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevoles,
            'metriques' => $metriques,
            'taches' => $taches,
            // fournir des tableaux simples pour le template
            'taches_proches' => $tachesProchesData
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
}