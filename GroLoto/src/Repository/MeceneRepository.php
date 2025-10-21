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
}
