<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard.html.twig', [
            'volunteers_count' => 127,
            'sponsors_count' => 34,
            'prizes_count' => 89,
            'stock_value' => 15420,
        ]);
    }
}
