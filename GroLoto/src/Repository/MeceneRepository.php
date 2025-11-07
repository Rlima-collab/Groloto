<?php
namespace App\Repository;

use App\Entity\Mecene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mecene>
 */
class MeceneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mecene::class);
    }

    /**
     * Retourne tous les mécènes avec leur utilisateur (si présent)
     * @return Mecene[]
     */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.utilisateur', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte tous les mécènes
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les mécènes récemment enregistrés (ex: derniers 30 jours)
     * @return Mecene[]
     */
    public function findRecentlyRegistered(int $days = 30): array
    {
        $since = new \DateTimeImmutable(sprintf('-%d days', $days));
        return $this->createQueryBuilder('m')
            ->leftJoin('m.utilisateur', 'u')
            ->andWhere('u.date_creation >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les mécènes ayant des lots pour un week-end spécifique
     * @return Mecene[]
     */
    public function findByWeekend($weekend): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.utilisateur', 'u')
            ->leftJoin('m.lots', 'l')
            ->leftJoin('l.evenement', 'e')
            ->addSelect('u', 'l', 'e')
            ->where('e.weekend = :weekend')
            ->setParameter('weekend', $weekend)
            ->groupBy('m.id')
            ->orderBy('m.organisation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

