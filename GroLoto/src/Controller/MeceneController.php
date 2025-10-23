<?php
namespace App\Controller;

use App\Entity\Mecene;
use App\Form\MeceneEditType;
use App\Repository\MeceneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MECENE')]
class MeceneController extends AbstractController
{
    #[Route('/mecenes', name: 'mecenes')]
    public function index(MeceneRepository $meceneRepository): Response
    {
        // Récupération de tous les mécènes depuis la base de données
        $mecenes = $meceneRepository->findAllWithUser();
        $totalMecenes = $meceneRepository->countAll();

        // Données pour les métriques
        $metriques = [
            'total' => $totalMecenes,
            'nouveaux' => 0, // Placeholder: pas de date de création dans la base
            'actifs' => $totalMecenes, // Placeholder for now
            'disponibles' => $totalMecenes // Placeholder for now
        ];

        return $this->render('mecenes.html.twig', [
            'mecenes' => $mecenes,
            'metriques' => $metriques
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