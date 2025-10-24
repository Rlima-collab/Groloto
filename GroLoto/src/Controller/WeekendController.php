<?php

namespace App\Controller;

use App\Entity\Weekend;
use App\Form\WeekendType;
use App\Repository\WeekendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/weekends')]
class WeekendController extends AbstractController
{
    #[Route('', name: 'app_weekends')]
    public function index(WeekendRepository $repo): Response
    {
        return $this->render('weekend/index.html.twig', [
            'weekends' => $repo->findBy([], ['date_vendredi' => 'DESC'])
        ]);
    }

    #[Route('/create', name: 'app_weekend_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $weekend = new Weekend();
        $form = $this->createForm(WeekendType::class, $weekend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateVendredi = $form->get('dateVendredi')->getData();

            $weekend->setDateVendredi($dateVendredi);
            $weekend->setDateSamedi((clone $dateVendredi)->modify('+1 day'));
            $weekend->setDateDimanche((clone $dateVendredi)->modify('+2 days'));

            $em->persist($weekend);
            $em->flush();

            $this->addFlash('success', 'Weekend créé !');
            return $this->redirectToRoute('app_weekends');
        }

        return $this->render('weekend/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}