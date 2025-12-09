<?php

namespace App\Repository;

use App\Entity\AffectationTache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffectationTache>
 */
class AffectationTacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffectationTache::class);
    }

    /**
     * Trouve toutes les affectations pour une tâche donnée
     */
    public function findByTache($tacheId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.tache = :tacheId')
            ->setParameter('tacheId', $tacheId)
            ->orderBy('a.date_affectation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les affectations pour un bénévole donné
     */
    public function findByBenevole($benevoleId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.benevole = :benevoleId')
            ->setParameter('benevoleId', $benevoleId)
            ->orderBy('a.date_affectation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de bénévoles assignés à une tâche
     */
    public function countBenevolesByTache($tacheId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.tache = :tacheId')
            ->setParameter('tacheId', $tacheId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de bénévoles effectivement assignés (statut 'assigne') à une tâche
     */
    public function countBenevolesAssignesByTache($tacheId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.tache = :tacheId')
            ->andWhere('a.statut = :statut')
            ->setParameter('tacheId', $tacheId)
            ->setParameter('statut', 'assigne')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve une affectation spécifique (tâche + bénévole)
     */
    public function findOneByTacheAndBenevole($tacheId, $benevoleId): ?AffectationTache
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.tache = :tacheId')
            ->andWhere('a.benevole = :benevoleId')
            ->setParameter('tacheId', $tacheId)
            ->setParameter('benevoleId', $benevoleId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les affectations qui chevauchent une période donnée pour un bénévole
     */
    public function findOverlappingAssignments($benevole, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.tache', 't')
            ->andWhere('a.benevole = :benevole')
            ->andWhere('t.debut < :end')
            ->andWhere('t.fin > :start')
            ->setParameter('benevole', $benevole)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
