<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Trouve tous les événements ordonnés par date
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les événements pour une période donnée
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date_debut >= :start')
            ->andWhere('e.date_fin <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les événements à venir
     */
    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date_debut >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('e.date_debut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les événements par nom
     */
    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%')
            ->orderBy('e.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les événements futurs et récents (max 1 semaine dans le passé)
     */
    public function findRecentAndUpcoming(): array
    {
        $oneWeekAgo = new \DateTime('-1 week');
        
        return $this->createQueryBuilder('e')
            ->where('e.date_debut >= :oneWeekAgo')
            ->setParameter('oneWeekAgo', $oneWeekAgo)
            ->orderBy('e.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}