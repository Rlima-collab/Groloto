<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $firstDayOfMonth = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
        $firstDayOfNextMonth = (new \DateTime('first day of next month'))->format('Y-m-d 00:00:00');

        // Nombre de bénévoles
        $volunteersCount = $this->entityManager->createQuery(
            'SELECT COUNT(b.id) FROM App\Entity\Benevole b WHERE b.actif = :actif'
        )
        ->setParameter('actif', true)
        ->getSingleScalarResult();

        // Nouveaux bénévoles ce mois
        $newVolunteers = $this->entityManager->createQuery(
            'SELECT COUNT(b.id) FROM App\Entity\Benevole b
             JOIN b.utilisateur u
             WHERE u.date_creation >= :firstDay AND u.date_creation < :nextMonth'
        )
        ->setParameters(['firstDay' => $firstDayOfMonth, 'nextMonth' => $firstDayOfNextMonth])
        ->getSingleScalarResult();

        // Nombre de mécènes
        $sponsorsCount = $this->entityManager->createQuery(
            'SELECT COUNT(m.id) FROM App\Entity\Mecene m'
        )
        ->getSingleScalarResult();

        // Nouveaux mécènes ce mois
        $newSponsors = $this->entityManager->createQuery(
            'SELECT COUNT(m.id) FROM App\Entity\Mecene m
             LEFT JOIN m.utilisateur u
             WHERE u.date_creation >= :firstDay AND u.date_creation < :nextMonth'
        )
        ->setParameters(['firstDay' => $firstDayOfMonth, 'nextMonth' => $firstDayOfNextMonth])
        ->getSingleScalarResult();

        // Nombre total de lots
        $prizesCount = $this->entityManager->createQuery(
            'SELECT SUM(l.quantite) FROM App\Entity\Lot l'
        )
        ->getSingleScalarResult();

        // Lots en rupture
        $outOfStock = $this->entityManager->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantite <= s.seuil'
        )
        ->getSingleScalarResult();

        // Valeur totale du stock
        $stockValue = $this->entityManager->createQuery(
            'SELECT SUM(s.quantite * s.valeur_unitaire) FROM App\Entity\Stock s'
        )
        ->getSingleScalarResult();

        // Prochains événements
        $events = $this->entityManager->createQuery(
            'SELECT e FROM App\Entity\Evenement e
             WHERE e.date_debut >= :today
             ORDER BY e.date_debut ASC'
        )
        ->setParameter('today', new \DateTime())
        ->setMaxResults(3)
        ->getResult();

        // Historique des événements
        $eventHistory = $this->entityManager->createQuery(
            'SELECT h, e, u FROM App\Entity\HistoriqueEvenement h
             JOIN h.evenement e
             LEFT JOIN h.utilisateur u
             ORDER BY h.date_action DESC'
        )
        ->setMaxResults(5)
        ->getResult();

        return $this->render('dashboard.html.twig', [
            'volunteers_count' => $volunteersCount ?? 0,
            'new_volunteers' => $newVolunteers ?? 0,
            'sponsors_count' => $sponsorsCount ?? 0,
            'new_sponsors' => $newSponsors ?? 0,
            'prizes_count' => $prizesCount ?? 0,
            'out_of_stock' => $outOfStock ?? 0,
            'stock_value' => $stockValue ?? 0,
            'events' => $events,
            'event_history' => $eventHistory,
        ]);
    }
}