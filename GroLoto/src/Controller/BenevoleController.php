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
        $totalBenevoles = $benevoleRepository->countActive();
        $nouveauxBenevoles = count($benevoleRepository->findRecentlyRegistered());

        $metriques = [
            'total' => $totalBenevoles,
            'nouveaux' => $nouveauxBenevoles,
            'actifs' => $totalBenevoles,
            'disponibles' => $totalBenevoles
        ];

        // Récupération des tâches futures (une seule fois)
        $tachesBrutes = $tacheRepository->findTachesFutures();

        // Formatage pour FullCalendar
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
                    'evenement' => $tache->getEvenement()?->getNom(),
                    'poste_requis' => $tache->getPosteRequis(),
                    'max_personnes' => $tache->getMaxPersonnes(),
                    'remarques' => $tache->getRemarque()
                ]
            ];
        }

        // Tâches proches (optionnel : 5 prochaines)
        $tachesProches = $tacheRepository->findTachesProches(5);

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevoles,
            'metriques' => $metriques,
            'taches' => $taches,
            'taches_proches' => $tachesProches
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
        $form->get('email')-> devisData($utilisateur->getEmail());
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