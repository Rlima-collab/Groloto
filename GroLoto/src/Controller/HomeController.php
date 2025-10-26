<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Rediriger vers le dashboard si connecté, sinon vers la page de connexion
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->redirectToRoute('dashboard');
    }
}
