<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\HistoriqueStock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class StockController extends AbstractController
{
    #[Route('/stocks', name: 'stocks')]
    public function index(EntityManagerInterface $em): Response
    {
        // --- Inventaire ---
        $stockItems = $em->getRepository(Stock::class)->findAll();
        $total_items = array_sum(array_map(fn($i) => $i->getQuantite(), $stockItems));
        $stock_value = array_sum(array_map(fn($i) => $i->getQuantite() * $i->getValeurUnitaire(), $stockItems));
        $low_stock_count = count(array_filter($stockItems, fn($i) => $i->getQuantite() <= $i->getSeuil()));
        $categories_count = count(array_unique(array_map(fn($i) => $i->getCategorie(), $stockItems)));

        // --- Historique regroupé par année ---
        $historiqueRepo = $em->getRepository(HistoriqueStock::class);
        $historiqueBrut = $historiqueRepo->findAll();

        $historique_par_annee = [];

        foreach ($historiqueBrut as $histo) {
            $annee = $histo->getDateCreation()?->format('Y') ?? 'Inconnue';
            if (!isset($historique_par_annee[$annee])) {
                $historique_par_annee[$annee] = [
                    'annee' => $annee,
                    'nb_articles' => 0,
                    'valeur_totale' => 0,
                    'nb_mouvements' => 0
                ];
            }

            $historique_par_annee[$annee]['nb_articles']++;
            $historique_par_annee[$annee]['nb_mouvements']++;
            // Valeur estimée approximative : quantité * valeur_unitaire du stock associé
            $stock = $histo->getStock();
            if ($stock) {
                $historique_par_annee[$annee]['valeur_totale'] += $histo->getQuantite() * $stock->getValeurUnitaire();
            }
        }

        // Trie par année décroissante
        krsort($historique_par_annee);

        return $this->render('stocks/index.html.twig', [
            'stock_items' => $stockItems,
            'total_items' => $total_items,
            'stock_value' => $stock_value,
            'low_stock_count' => $low_stock_count,
            'categories_count' => $categories_count,
            'historique_par_annee' => $historique_par_annee
        ]);
    }

    #[Route('/stocks/export-historique/{annee}', name: 'stocks_export_historique')]
    public function exportHistoriquePDF(string $annee, EntityManagerInterface $em): Response
    {
        // Récupération de l'historique pour l'année demandée
        $historiques = $em->getRepository(HistoriqueStock::class)->findAll();
        
        $historiquesAnnee = [];
        $nbArticles = [];
        $valeurTotale = 0;
        $nbMouvements = 0;
        
        foreach ($historiques as $histo) {
            $anneeHisto = $histo->getDateCreation()?->format('Y');
            if ($anneeHisto === $annee) {
                $historiquesAnnee[] = $histo;
                $nbMouvements++;
                
                $stock = $histo->getStock();
                if ($stock) {
                    $stockId = $stock->getId();
                    if (!in_array($stockId, $nbArticles)) {
                        $nbArticles[] = $stockId;
                    }
                    $valeurTotale += $histo->getQuantite() * $stock->getValeurUnitaire();
                }
            }
        }
        
        // Tri par date décroissante
        usort($historiquesAnnee, function($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });
        
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Génération du HTML depuis un template Twig
        $html = $this->renderView('stocks/historique_pdf.html.twig', [
            'annee' => $annee,
            'historiques' => $historiquesAnnee,
            'nb_articles' => count($nbArticles),
            'valeur_totale' => $valeurTotale,
            'nb_mouvements' => $nbMouvements,
            'date_generation' => new \DateTime()
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Retour du PDF en téléchargement
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="historique_stock_' . $annee . '.pdf"'
            ]
        );
    }

    #[Route('/stocks/ajouter', name: 'stock_ajouter_mouvement', methods: ['POST'])]
    public function ajouterMouvement(Request $request, EntityManagerInterface $em): Response
    {
        $articleId = $request->request->get('article');
        $type = $request->request->get('type');
        $quantite = (int)$request->request->get('quantite');
        $raison = $request->request->get('raison');
        $notes = $request->request->get('notes');

        $stock = $em->getRepository(\App\Entity\Stock::class)->find($articleId);
        if (!$stock) {
            $this->addFlash('error', 'Article introuvable.');
            return $this->redirectToRoute('stocks');
        }

        // Mise à jour du stock
        if ($type === 'entree') {
            $stock->setQuantite($stock->getQuantite() + $quantite);
        } elseif ($type === 'sortie') {
            $stock->setQuantite(max(0, $stock->getQuantite() - $quantite));
        }

        // Enregistrer dans l’historique
        $historique = new \App\Entity\HistoriqueStock();
        $historique->setStock($stock);
        $historique->setTypeChangement($type);
        $historique->setQuantite($quantite);
        $historique->setRaison($raison);
        $historique->setDateCreation(new \DateTime());
        $historique->setUtilisateur($this->getUser() ?? null);

        $em->persist($stock);
        $em->persist($historique);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistré avec succès');
        return $this->redirectToRoute('stocks');
    }

    #[Route('/stocks/export-inventaire', name: 'stocks_export_inventaire')]
    public function exportInventaire(EntityManagerInterface $em): Response
    {
        $stockItems = $em->getRepository(Stock::class)->findAll();
        
        // Créer le contenu CSV
        $csv = "Article,Catégorie,Stock,Seuil,Valeur Unitaire,Unité\n";
        
        foreach ($stockItems as $item) {
            $csv .= sprintf(
                '"%s","%s",%d,%d,%.2f,"%s"' . "\n",
                str_replace('"', '""', $item->getNom()),
                $item->getCategorie(),
                $item->getQuantite(),
                $item->getSeuil(),
                $item->getValeurUnitaire(),
                $item->getUnite()
            );
        }
        
        // Retourner le CSV en téléchargement
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="inventaire_stock_' . date('Y-m-d') . '.csv"');
        
        return $response;
    }

}
