<?php
namespace App\Repository;

use App\Entity\Lot;
use App\Entity\Mecene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lot>
 */
class LotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lot::class);
    }

    /**
     * Retourne tous les lots avec leur mécène
     * @return Lot[]
     */
    public function findAllWithMecene(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.mecene', 'm')
            ->leftJoin('m.utilisateur', 'u')
            ->addSelect('m')
            ->addSelect('u')
            ->orderBy('l.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les lots d'un mécène spécifique
     * @return Lot[]
     */
    public function findByMecene(Mecene $mecene): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.mecene = :mecene')
            ->setParameter('mecene', $mecene)
            ->orderBy('l.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte tous les lots
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte tous les lots d'un mécène
     */
    public function countByMecene(Mecene $mecene): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.mecene = :mecene')
            ->setParameter('mecene', $mecene)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule la valeur totale des lots
     */
    public function getTotalValue(): float
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.valeur_estimee * l.quantite)')
            ->getQuery()
            ->getSingleScalarResult();
        
        return (float) ($result ?? 0);
    }

    /**
     * Calcule la valeur totale des lots d'un mécène
     */
    public function getTotalValueByMecene(Mecene $mecene): float
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.valeur_estimee * l.quantite)')
            ->where('l.mecene = :mecene')
            ->setParameter('mecene', $mecene)
            ->getQuery()
            ->getSingleScalarResult();
        
        return (float) ($result ?? 0);
    }
}
