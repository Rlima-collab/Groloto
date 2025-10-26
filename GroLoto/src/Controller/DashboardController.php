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

    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        $firstDayOfMonth = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
        $firstDayOfNextMonth = (new \DateTime('first day of next month'))->format('Y-m-d 00:00:00');

        // Données communes
        $volunteersCount = $this->entityManager->createQuery(
            'SELECT COUNT(b.id) FROM App\Entity\Benevole b WHERE b.actif = :actif'
        )
        ->setParameter('actif', true)
        ->getSingleScalarResult();

        $newVolunteers = $this->entityManager->createQuery(
            'SELECT COUNT(b.id) FROM App\Entity\Benevole b
             JOIN b.utilisateur u
             WHERE u.date_creation >= :firstDay AND u.date_creation < :nextMonth'
        )
        ->setParameters(['firstDay' => $firstDayOfMonth, 'nextMonth' => $firstDayOfNextMonth])
        ->getSingleScalarResult();

        $sponsorsCount = $this->entityManager->createQuery(
            'SELECT COUNT(m.id) FROM App\Entity\Mecene m'
        )
        ->getSingleScalarResult();

        $prizesCount = $this->entityManager->createQuery(
            'SELECT SUM(l.quantite) FROM App\Entity\Lot l'
        )
        ->getSingleScalarResult();

        // Vérification de l'existence de l'entité Stock
        $outOfStock = 0;
        try {
            $outOfStock = $this->entityManager->createQuery(
                'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantite <= s.seuil'
            )
            ->getSingleScalarResult();
        } catch (\Exception $e) {
            // Si l'entité Stock ou les champs n'existent pas, retourner 0
            $outOfStock = 0;
        }

        $stockValue = 0;
        try {
            $stockValue = $this->entityManager->createQuery(
                'SELECT SUM(s.quantite * s.valeur_unitaire) FROM App\Entity\Stock s'
            )
            ->getSingleScalarResult();
        } catch (\Exception $e) {
            // Si l'entité Stock ou les champs n'existent pas, retourner 0
            $stockValue = 0;
        }

        $upcomingEvents = $this->entityManager->createQuery(
            'SELECT e FROM App\Entity\Evenement e
             WHERE e.date_debut >= :today
             ORDER BY e.date_debut ASC'
        )
        ->setParameter('today', new \DateTime())
        ->setMaxResults(5)
        ->getResult();

        // Données spécifiques par rôle
        $roleData = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            // Remplacement de pending_registrations par le nombre total d'utilisateurs
            $totalUsers = $this->entityManager->createQuery(
                'SELECT COUNT(u.id) FROM App\Entity\Utilisateur u'
            )
            ->getSingleScalarResult();
            $roleData = ['total_users' => $totalUsers ?? 0];
        } elseif ($this->isGranted('ROLE_MECENE')) {
            $user = $this->getUser();
            $sponsorContributions = 0;
            if ($user) {
                $sponsorContributions = $this->entityManager->createQuery(
                    'SELECT COALESCE(SUM(l.valeur_estimee * l.quantite), 0) FROM App\Entity\Lot l
                     JOIN l.mecene m
                     JOIN m.utilisateur u
                     WHERE u.id = :userId'
                )
                ->setParameter('userId', $user->getId())
                ->getSingleScalarResult();
            }
            $roleData = ['sponsor_contributions' => $sponsorContributions];
        } elseif ($this->isGranted('ROLE_BENEVOLE')) {
            $user = $this->getUser();
            $pendingTasks = [];
            if ($user) {
                try {
                    $pendingTasks = $this->entityManager->createQuery(
                        'SELECT t FROM App\Entity\Tache t
                         JOIN t.benevole b
                         JOIN b.utilisateur u
                         WHERE u.id = :userId AND t.statut = :pending'
                    )
                    ->setParameters(['userId' => $user->getId(), 'pending' => 'en_attente'])
                    ->setMaxResults(5)
                    ->getResult();
                } catch (\Exception $e) {
                    // Si l'entité Tache ou les champs n'existent pas, retourner un tableau vide
                    $pendingTasks = [];
                }
            }
            $roleData = ['pending_tasks' => $pendingTasks];
        } else {
            $publicEvents = $this->entityManager->createQuery(
                'SELECT e FROM App\Entity\Evenement e
                 WHERE e.date_debut >= :today
                 ORDER BY e.date_debut ASC'
            )
            ->setParameter('today', new \DateTime())
            ->setMaxResults(5)
            ->getResult();
            $roleData = ['public_events' => $publicEvents];
        }

        return $this->render('dashboard.html.twig', [
            'volunteers_count' => $volunteersCount ?? 0,
            'new_volunteers' => $newVolunteers ?? 0,
            'sponsors_count' => $sponsorsCount ?? 0,
            'prizes_count' => $prizesCount ?? 0,
            'out_of_stock' => $outOfStock ?? 0,
            'stock_value' => $stockValue ?? 0,
            'upcoming_events' => $upcomingEvents,
            'role_data' => $roleData,
        ]);
    }
}