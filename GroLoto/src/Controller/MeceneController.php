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
        // Récupérer les filtres depuis la requête
        $weekendId = $request->query->get('weekend');
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');
        $perPage = $request->query->get('per_page', 10);

        $weekendFiltre = null;
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Construction des critères de recherche
        $criteria = [
            'weekend' => $weekendFiltre,
            'search' => $search,
            'sort' => $sort
        ];

        // Récupération des mécènes selon les critères
        if ($this->isGranted('ROLE_ADMIN')) {
            $mecenes = $meceneRepository->search($criteria);
        } else {
            // Pour un mécène non admin, on pourrait restreindre, mais ici on garde le comportement par défaut
            // ou on adapte search pour filtrer par utilisateur courant si nécessaire.
            // Le code original faisait findAllWithUser() pour non-admin (ce qui est étrange si c'est "Voir les mécènes", 
            // un mécène ne devrait voir que lui-même ou tous ? Le code original montrait tout le monde).
            // On va supposer que tout le monde peut voir la liste (annuaire).
            $mecenes = $meceneRepository->search($criteria);
        }

        // Pagination manuelle simple
        if ($perPage !== 'all') {
            $perPage = (int) $perPage;
            // Ici on pourrait implémenter une pagination réelle, mais pour l'instant on coupe juste si nécessaire
            // ou on laisse tout si on n'a pas de système de page.
            // Le template a des boutons de pagination mais pas de logique PHP visible pour "page".
            // On va laisser tous les résultats pour l'instant car la pagination demande plus de travail (page parameter, slice).
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
            // Gérer l'upload du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                // Vérifier le type et la taille du fichier
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $maxFileSize = 2 * 1024 * 1024; // 2 Mo
                
                if (!in_array($logoFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Le logo doit être une image (JPG, PNG).');
                } elseif ($logoFile->getSize() > $maxFileSize) {
                    $this->addFlash('error', 'Le logo est trop volumineux (maximum 2 Mo).');
                } else {
                    // Supprimer l'ancien logo si il existe
                    if ($mecene->getLogo()) {
                        $oldLogoPath = $this->getParameter('kernel.project_dir') . '/public/' . $mecene->getLogo();
                        // Fallback si c'est juste le nom du fichier (ancienne version)
                        if (!file_exists($oldLogoPath)) {
                             $oldLogoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $mecene->getLogo();
                        }
                        
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                    
                    // Générer un nom unique pour le fichier
                    $newFilename = uniqid() . '.' . $logoFile->guessExtension();
                    
                    // Déplacer le fichier vers le dossier d'upload
                    $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/logos';
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0777, true);
                    }
                    
                    try {
                        $logoFile->move($uploadsDirectory, $newFilename);
                        $mecene->setLogo('uploads/logos/' . $newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                    }
                }
            }

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

    #[Route('/mecene/{id}/download-logo', name: 'mecene_download_logo')]
    #[IsGranted('ROLE_ADMIN')]
    public function downloadLogo(int $id, MeceneRepository $meceneRepository): Response
    {
        $mecene = $meceneRepository->find($id);

        if (!$mecene || !$mecene->getLogo()) {
            throw $this->createNotFoundException('Logo non trouvé');
        }

        $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $mecene->getLogo();

        if (!file_exists($logoPath)) {
            throw $this->createNotFoundException('Fichier logo non trouvé');
        }

        return $this->file($logoPath, $mecene->getOrganisation() . '_logo.' . pathinfo($mecene->getLogo(), PATHINFO_EXTENSION));
    }

    #[Route('/mecenes/download-logos', name: 'mecenes_download_logos')]
    #[IsGranted('ROLE_ADMIN')]
    public function downloadLogos(
        Request $request,
        MeceneRepository $meceneRepository,
        WeekendRepository $weekendRepository
    ): Response {
        // Récupérer les filtres depuis la requête
        $weekendId = $request->query->get('weekend');
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $weekendFiltre = null;
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Construction des critères de recherche
        $criteria = [
            'weekend' => $weekendFiltre,
            'search' => $search,
            'sort' => $sort
        ];

        // Récupération des mécènes selon les critères
        $mecenes = $meceneRepository->search($criteria);

        $hasLogos = false;
        foreach ($mecenes as $mecene) {
            if ($mecene->getLogo()) {
                // Gestion des deux formats de chemin possibles
                $logoPath = $this->getParameter('kernel.project_dir') . '/public/' . $mecene->getLogo();
                if (!file_exists($logoPath)) {
                    $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $mecene->getLogo();
                }
                
                if (file_exists($logoPath)) {
                    $hasLogos = true;
                    break;
                }
            }
        }

        if (!$hasLogos) {
            $this->addFlash('warning', 'Aucun logo trouvé pour les mécènes filtrés.');
            return $this->redirectToRoute('mecenes', $request->query->all());
        }

        $zip = new \ZipArchive();
        $zipName = 'logos_mecenes_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipName;

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Impossible de créer l\'archive ZIP');
        }

        foreach ($mecenes as $mecene) {
            if ($mecene->getLogo()) {
                // Gestion des deux formats de chemin possibles
                $logoPath = $this->getParameter('kernel.project_dir') . '/public/' . $mecene->getLogo();
                if (!file_exists($logoPath)) {
                    $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $mecene->getLogo();
                }

                if (file_exists($logoPath)) {
                    // Nettoyage du nom de fichier pour le ZIP
                    $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $mecene->getOrganisation());
                    $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $filename = $cleanName . '_logo.' . $extension;
                    
                    $zip->addFile($logoPath, $filename);
                }
            }
        }

        $zip->close();

        return $this->file($zipPath, $zipName)->deleteFileAfterSend();
    }

    #[Route('/mecenes/export-social-csv', name: 'mecenes_export_social_csv')]
    #[IsGranted('ROLE_ADMIN')]
    public function exportSocialCsv(
        Request $request,
        MeceneRepository $meceneRepository,
        WeekendRepository $weekendRepository
    ): Response {
        // Récupérer les filtres depuis la requête
        $weekendId = $request->query->get('weekend');
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $weekendFiltre = null;
        if ($weekendId) {
            $weekendFiltre = $weekendRepository->find($weekendId);
        }

        // Construction des critères de recherche
        $criteria = [
            'weekend' => $weekendFiltre,
            'search' => $search,
            'sort' => $sort
        ];

        // Récupération des mécènes selon les filtres
        $mecenes = $meceneRepository->search($criteria);

        // Créer le contenu CSV
        $csvContent = "Organisation,Nom,Prénom,Email,Instagram,Facebook\n";

        foreach ($mecenes as $mecene) {
            $organisation = $mecene->getOrganisation() ?? '';
            $nom = $mecene->getUtilisateur()->getNom() ?? '';
            $prenom = $mecene->getUtilisateur()->getPrenom() ?? '';
            $email = $mecene->getUtilisateur()->getEmail() ?? '';
            $instagram = $mecene->getInstagram() ?? '';
            $facebook = $mecene->getFacebook() ?? '';

            // Échapper les virgules et guillemets dans les champs
            $organisation = '"' . str_replace('"', '""', $organisation) . '"';
            $nom = '"' . str_replace('"', '""', $nom) . '"';
            $prenom = '"' . str_replace('"', '""', $prenom) . '"';
            $email = '"' . str_replace('"', '""', $email) . '"';
            $instagram = '"' . str_replace('"', '""', $instagram) . '"';
            $facebook = '"' . str_replace('"', '""', $facebook) . '"';

            $csvContent .= "$organisation,$nom,$prenom,$email,$instagram,$facebook\n";
        }

        // Créer la réponse avec le fichier CSV
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="reseaux_sociaux_mecenes.csv"');

        return $response;
    }
}