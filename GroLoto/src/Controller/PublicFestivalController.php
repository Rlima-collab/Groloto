<?php
namespace App\Controller;

use App\Entity\Weekend;
use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicFestivalController extends AbstractController
{
    #[Route('/festivals', name: 'public_festivals')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer les weekends à partir d'aujourd'hui (pas les passés), triés par date croissante
        $today = new \DateTime('today');
        $weekends = $em->getRepository(Weekend::class)->createQueryBuilder('w')
            ->where('w.date_fin >= :today')
            ->setParameter('today', $today)
            ->orderBy('w.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
        
        return $this->render('public_festivals/index.html.twig', [
            'weekends' => $weekends,
        ]);
    }

    #[Route('/festivals/{id}', name: 'public_festival_detail')]
    public function detail(EntityManagerInterface $em, $id): Response
    {
        $weekend = $em->getRepository(Weekend::class)->find($id);
        if (!$weekend) {
            throw $this->createNotFoundException('Weekend non trouvé');
        }
        $evenements = $em->getRepository(Evenement::class)->findBy(
            ['weekend' => $weekend],
            ['date_debut' => 'ASC', 'heure_debut' => 'ASC']
        );
        return $this->render('public_festivals/detail.html.twig', [
            'weekend' => $weekend,
            'evenements' => $evenements,
        ]);
    }
}
