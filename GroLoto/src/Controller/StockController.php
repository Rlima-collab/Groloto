<?php

namespace App\Controller;

use App\Entity\Stock; // ✅ on importe l’entité
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stocks', name: 'stocks')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupération de tous les stocks en BDD
        $stockItems = $em->getRepository(Stock::class)->findAll();

        // Calculs dynamiques
        $total_items = array_sum(array_map(fn($i) => $i->getQuantite(), $stockItems));

        $stock_value = array_sum(array_map(
            fn($i) => $i->getQuantite() * $i->getValeurUnitaire(),
            $stockItems
        ));

        $low_stock_count = count(array_filter(
            $stockItems,
            fn($i) => $i->getQuantite() <= $i->getSeuil()
        ));

        $categories_count = count(array_unique(array_map(
            fn($i) => $i->getCategorie(),
            $stockItems
        )));

        return $this->render('stocks.html.twig', [
            'stock_items'      => $stockItems,
            'total_items'      => $total_items,
            'stock_value'      => $stock_value,
            'low_stock_count'  => $low_stock_count,
            'categories_count' => $categories_count,
        ]);
    }
}
