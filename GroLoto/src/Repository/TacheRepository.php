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

    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.weekend', 'w')
            ->addSelect('w')
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->findAll();
    }

    public function findTachesFutures(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.debut > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findTachesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\AffectationTache', 'a', 'WITH', 'a.tache = t.id')
            ->andWhere('a.benevole = :benevoleId')
            ->setParameter('benevoleId', $benevoleId)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTachesFuturesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\AffectationTache', 'a', 'WITH', 'a.tache = t.id')
            ->andWhere('a.benevole = :benevoleId')
            ->andWhere('t.debut > :now')
            ->setParameter('benevoleId', $benevoleId)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTachesRealiseesForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\AffectationTache', 'a', 'WITH', 'a.tache = t.id')
            ->andWhere('a.benevole = :benevoleId')
            ->andWhere('t.fin < :now')
            ->setParameter('benevoleId', $benevoleId)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPosteRequis(string $poste): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.posteRequis = :poste')
            ->setParameter('poste', $poste)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.debut >= :start')
            ->andWhere('t.fin <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findByWeekend(int $weekendId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.weekend = :weekendId')
            ->setParameter('weekendId', $weekendId)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWeekendsForBenevole(int $benevoleId): array
    {
        return $this->createQueryBuilder('t')
            ->select('DISTINCT w.id, w.nom, w.date_debut, w.date_fin, w.description, w.cover_image')
            ->innerJoin('t.weekend', 'w')
            ->innerJoin('App\Entity\AffectationTache', 'a', 'WITH', 'a.tache = t.id')
            ->andWhere('a.benevole = :benevoleId')
            ->setParameter('benevoleId', $benevoleId)
            ->orderBy('w.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTachesForBenevoleByWeekend(int $benevoleId, int $weekendId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\AffectationTache', 'a', 'WITH', 'a.tache = t.id')
            ->andWhere('a.benevole = :benevoleId')
            ->andWhere('t.weekend = :weekendId')
            ->setParameter('benevoleId', $benevoleId)
            ->setParameter('weekendId', $weekendId)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}