<?php

namespace App\Repository;

use App\Entity\Creneau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CreneauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creneau::class);
    }

    /**
     * Trouve tous les créneaux avec leur événement associé
     */
    public function findAllWithEvent(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.evenement', 'e')
            ->addSelect('e')
            ->orderBy('c.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les créneaux pour une période donnée
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.evenement', 'e')
            ->addSelect('e')
            ->where('c.debut >= :start')
            ->andWhere('c.fin <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les créneaux par événement
     */
    public function findByEvenement(int $evenementId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.evenement = :evenement')
            ->setParameter('evenement', $evenementId)
            ->orderBy('c.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les créneaux qui tombent le week-end (vendredi, samedi, dimanche)
     */
    public function findWeekendSlots(): array
    {
        // Récupérer tous les créneaux puis filtrer en PHP
        $allCreneaux = $this->createQueryBuilder('c')
            ->leftJoin('c.evenement', 'e')
            ->addSelect('e')
            ->orderBy('c.debut', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Filtrer les créneaux pour ne garder que ceux du week-end
        return array_filter($allCreneaux, function($creneau) {
            $dayOfWeek = (int) $creneau->getDebut()->format('N'); // 1=lundi, 7=dimanche
            return in_array($dayOfWeek, [5, 6, 7]); // vendredi, samedi, dimanche
        });
    }

    /**
     * Trouve les prochains créneaux de week-end
     */
    public function findUpcomingWeekendSlots(int $limit = 5): array
    {
        // Récupérer les créneaux futurs puis filtrer en PHP
        $futureCreneaux = $this->createQueryBuilder('c')
            ->leftJoin('c.evenement', 'e')
            ->addSelect('e')
            ->where('c.debut >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.debut', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Filtrer pour ne garder que les créneaux du week-end
        $weekendCreneaux = array_filter($futureCreneaux, function($creneau) {
            $dayOfWeek = (int) $creneau->getDebut()->format('N'); // 1=lundi, 7=dimanche
            return in_array($dayOfWeek, [5, 6, 7]); // vendredi, samedi, dimanche
        });
        
        // Limiter le nombre de résultats
        return array_slice($weekendCreneaux, 0, $limit);
    }
}