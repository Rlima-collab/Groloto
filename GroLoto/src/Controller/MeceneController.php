<?php
namespace App\Controller;

use App\Entity\Mecene;
use App\Entity\Lot;
use App\Form\MeceneEditType;
use App\Repository\MeceneRepository;
use App\Repository\LotRepository;
use App\Repository\WeekendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MECENE')]
class MeceneController extends AbstractController
{
    /**
     * Page "Voir les mécènes" - Liste de tous les mécènes
     */
    #[Route('/mecenes', name: 'mecenes')]
    public function index(
        Request $request,
        MeceneRepository $meceneRepository,
        LotRepository $lotRepository,
        WeekendRepository $weekendRepository
    ): Response {
        // Récupérer le filtre week-end depuis la requête
        $weekendId = $request->query->get('weekend');
        $weekendFiltre = null;
        
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Récupération des mécènes selon le filtre
        if ($this->isGranted('ROLE_ADMIN') && $weekendFiltre) {
            $mecenes = $meceneRepository->findByWeekend($weekendFiltre);
        } else {
            $mecenes = $meceneRepository->findAllWithUser();
        }

        $totalMecenes = count($mecenes);
        $totalLots = $weekendFiltre ? 
            count($lotRepository->findByWeekend($weekendFiltre)) : 
            $lotRepository->countAll();

        // Données pour les métriques
        $metriques = [
            'total' => $totalMecenes,
            'nouveaux' => 0, // Placeholder: pas de date de création dans la base
            'actifs' => $totalMecenes,
            'disponibles' => $totalLots
        ];

        // Récupérer tous les week-ends pour le filtre
        $weekends = $this->isGranted('ROLE_ADMIN') ? $weekendRepository->findAll() : [];

        return $this->render('mecenes.html.twig', [
            'mecenes' => $mecenes,
            'metriques' => $metriques,
            'weekends' => $weekends,
            'weekend_filtre' => $weekendFiltre
        ]);
    }

    /**
     * Page "Voir les lots" - Liste de tous les lots
     */
    #[Route('/mecene/lots', name: 'mecene_lots')]
    public function lots(
        Request $request,
        MeceneRepository $meceneRepository,
        LotRepository $lotRepository,
        WeekendRepository $weekendRepository
    ): Response {
        // Bloquer l'accès aux mécènes
        if ($this->isGranted('ROLE_MECENE') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs et bénévoles.');
        }
        
        $utilisateur = $this->getUser();
        $mecene = $meceneRepository->findOneBy(['utilisateur' => $utilisateur]);

        // Récupérer le filtre week-end depuis la requête
        $weekendId = $request->query->get('weekend');
        $weekendFiltre = null;
        
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Si admin, afficher tous les lots, sinon seulement ceux du mécène connecté
        if ($this->isGranted('ROLE_ADMIN')) {
            if ($weekendFiltre) {
                $lots = $lotRepository->findByWeekend($weekendFiltre);
                $valeurTotale = $lotRepository->getTotalValueByWeekend($weekendFiltre);
            } else {
                $lots = $lotRepository->findAllWithMecene();
                $valeurTotale = $lotRepository->getTotalValue();
            }
            $nombreLots = count($lots);
        } elseif ($mecene) {
            $lots = $lotRepository->findByMecene($mecene);
            $valeurTotale = $lotRepository->getTotalValueByMecene($mecene);
            $nombreLots = $lotRepository->countByMecene($mecene);
        } else {
            $lots = [];
            $valeurTotale = 0;
            $nombreLots = 0;
        }

        // Statistiques
        $metriques = [
            'total' => $nombreLots,
            'valeur' => $valeurTotale,
            'mecenes' => $this->isGranted('ROLE_ADMIN') ? 
                count(array_unique(array_map(fn($lot) => $lot->getMecene()?->getId(), $lots))) : 1
        ];

        // Récupérer tous les week-ends pour le filtre
        $weekends = $this->isGranted('ROLE_ADMIN') ? $weekendRepository->findAll() : [];

        return $this->render('mecenes/lots.html.twig', [
            'lots' => $lots,
            'mecene' => $mecene,
            'metriques' => $metriques,
            'valeur_totale' => $valeurTotale,
            'weekends' => $weekends,
            'weekend_filtre' => $weekendFiltre
        ]);
    }

    /**
     * Page "Voir les lots d'un mécène" - Lots d'un mécène spécifique
     */
    #[Route('/mecenes/{id}/lots', name: 'mecene_voir_lots')]
    public function voirLotsMecene(
        int $id,
        Request $request,
        MeceneRepository $meceneRepository,
        LotRepository $lotRepository,
        WeekendRepository $weekendRepository
    ): Response {
        $mecene = $meceneRepository->find($id);

        if (!$mecene) {
            $this->addFlash('error', 'Mécène non trouvé.');
            return $this->redirectToRoute('mecenes');
        }

        // Vérifier les permissions : admin ou le mécène lui-même
        $utilisateur = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $mecene->getUtilisateur() !== $utilisateur) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette page.');
            return $this->redirectToRoute('dashboard');
        }

        // Récupérer le filtre week-end depuis la requête
        $weekendId = $request->query->get('weekend');
        $weekendFiltre = null;
        
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Récupérer les lots selon le filtre
        if ($weekendFiltre) {
            // Filtrer par weekend ET par mécène
            $allLotsByWeekend = $lotRepository->findByWeekend($weekendFiltre);
            $lots = array_filter($allLotsByWeekend, fn($lot) => $lot->getMecene() === $mecene);
        } else {
            $lots = $lotRepository->findByMecene($mecene);
        }

        $valeurTotale = array_sum(array_map(fn($lot) => $lot->getValeurEstimee() * $lot->getQuantite(), $lots));
        $nombreLots = count($lots);

        // Statistiques
        $metriques = [
            'total' => $nombreLots,
            'valeur' => $valeurTotale,
            'mecenes' => 1
        ];

        // Récupérer tous les week-ends pour le filtre
        $weekends = $weekendRepository->findAll();

        return $this->render('mecenes/lots_mecene.html.twig', [
            'lots' => $lots,
            'mecene' => $mecene,
            'metriques' => $metriques,
            'valeur_totale' => $valeurTotale,
            'weekends' => $weekends,
            'weekend_filtre' => $weekendFiltre
        ]);
    }

    #[Route('/mecenes/{id}/edit', name: 'mecene_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id,
        Request $request,
        MeceneRepository $meceneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $mecene = $meceneRepository->find($id);

        if (!$mecene) {
            $this->addFlash('error', 'Mécène non trouvé.');
            return $this->redirectToRoute('mecenes');
        }

        $utilisateur = $mecene->getUtilisateur();

        $form = $this->createForm(MeceneEditType::class, $mecene);
        
        // Pré-remplir les champs de l'utilisateur
        if ($utilisateur) {
            $form->get('prenom')->setData($utilisateur->getPrenom());
            $form->get('nom')->setData($utilisateur->getNom());
            $form->get('email')->setData($utilisateur->getEmail());
            $form->get('telephone')->setData($utilisateur->getTelephone());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les données de l'utilisateur
            if ($utilisateur) {
                $utilisateur->setPrenom($form->get('prenom')->getData());
                $utilisateur->setNom($form->get('nom')->getData());
                $utilisateur->setEmail($form->get('email')->getData());
                $utilisateur->setTelephone($form->get('telephone')->getData());
                $utilisateur->setDateModification(new \DateTime());
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le mécène a été mis à jour avec succès !');
                return $this->redirectToRoute('mecenes');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du mécène.');
            }
        }

        return $this->render('mecenes/edit.html.twig', [
            'form' => $form->createView(),
            'mecene' => $mecene,
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/mecenes/{id}/delete', name: 'mecene_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        Request $request,
        MeceneRepository $meceneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $mecene = $meceneRepository->find($id);

        if (!$mecene) {
            $this->addFlash('error', 'Mécène non trouvé.');
            return $this->redirectToRoute('mecenes');
        }

        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_mecene_' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('mecenes');
        }

        try {
            $organisation = $mecene->getOrganisation();
            
            // Supprimer le mécène
            $entityManager->remove($mecene);
            $entityManager->flush();
            
            $this->addFlash('success', "Le mécène {$organisation} a été supprimé avec succès.");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du mécène.');
        }

        return $this->redirectToRoute('mecenes');
    }
}