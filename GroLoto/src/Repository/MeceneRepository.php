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
     * Recherche les mécènes selon des critères
     * @param array $criteria
     * @return Mecene[]
     */
    public function search(array $criteria): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.utilisateur', 'u')
            ->addSelect('u');

        if (!empty($criteria['weekend'])) {
            $qb->leftJoin('m.lots', 'l')
               ->andWhere('l.weekend = :weekend')
               ->setParameter('weekend', $criteria['weekend']);
        }

        if (!empty($criteria['search'])) {
            $qb->andWhere('m.organisation LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['sort'])) {
            switch ($criteria['sort']) {
                case 'nom-asc':
                    $qb->orderBy('u.nom', 'ASC');
                    break;
                case 'nom-desc':
                    $qb->orderBy('u.nom', 'DESC');
                    break;
                case 'organisation-asc':
                    $qb->orderBy('m.organisation', 'ASC');
                    break;
                case 'organisation-desc':
                    $qb->orderBy('m.organisation', 'DESC');
                    break;
                case 'date-asc':
                    $qb->orderBy('u.date_creation', 'ASC');
                    break;
                case 'date-desc':
                    $qb->orderBy('u.date_creation', 'DESC');
                    break;
                default:
                    $qb->orderBy('u.date_creation', 'DESC');
            }
        } else {
             $qb->orderBy('u.date_creation', 'DESC');
        }

        return $qb->getQuery()->getResult();
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
            ->leftJoin('l.weekend', 'w')
            ->addSelect('u', 'l', 'w')
            ->where('w.id = :weekend')
            ->setParameter('weekend', $weekend)
            ->groupBy('m.id')
            ->orderBy('m.organisation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

