<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 *
 * @method Tache|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tache|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tache[]    findAll()
 * @method Tache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    /**
     * Récupère toutes les tâches triées par date de début
     */
    public function findAll(): array
    {
        return $this->findBy([], ['debut' => 'ASC']);
    }

    /**
     * Récupère les tâches futures (début > maintenant)
     */
    public function findTachesFutures(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.debut > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les prochaines tâches (limitées)
     */
    public function findTachesProches(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.debut > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.debut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}