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
     * Trouve les tâches futures assignées à un bénévole
     */
    public function findTachesFuturesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\\Entity\\AffectationTache', 'a', 'WITH', 'a.tache = t')
            ->andWhere('IDENTITY(a.benevole) = :benevoleId')
            ->andWhere('t.debut > :now')
            ->setParameter('benevoleId', $benevoleId)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les tâches assignées à un bénévole
     */
    public function findTachesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\\Entity\\AffectationTache', 'a', 'WITH', 'a.tache = t')
            ->andWhere('IDENTITY(a.benevole) = :benevoleId')
            ->setParameter('benevoleId', $benevoleId)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches réalisées (fin < now) assignées à un bénévole
     */
    public function findTachesRealiseesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\\Entity\\AffectationTache', 'a', 'WITH', 'a.tache = t')
            ->andWhere('IDENTITY(a.benevole) = :benevoleId')
            ->andWhere('t.fin < :now')
            ->setParameter('benevoleId', $benevoleId)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches par poste requis
     */
    public function findByPosteRequis(string $poste): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.poste_requis = :poste')
            ->setParameter('poste', $poste)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches pour une période donnée
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.debut >= :start')
            ->andWhere('t.fin <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère TOUTES les tâches avec la relation événement chargée
     * → Remplace la méthode par défaut
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.evenement', 'e')
            ->addSelect('e')
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Alias pour findAll() avec relations
     */
    public function findAllWithRelations(): array
    {
        return $this->findAll();
    }
}