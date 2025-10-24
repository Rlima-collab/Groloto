<?php

namespace App\Repository;

use App\Entity\DemandeTache;
use App\Entity\Benevole;
use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DemandeTacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeTache::class);
    }

    /**
     * Trouver toutes les demandes en attente
     */
    public function findEnAttente(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('d.date_demande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les demandes d'un bénévole
     */
    public function findByBenevole(Benevole $benevole): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.benevole = :benevole')
            ->setParameter('benevole', $benevole)
            ->orderBy('d.date_demande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifier si un bénévole a déjà fait une demande pour une tâche
     */
    public function findByBenevoleAndTache(Benevole $benevole, Tache $tache): ?DemandeTache
    {
        return $this->createQueryBuilder('d')
            ->where('d.benevole = :benevole')
            ->andWhere('d.tache = :tache')
            ->andWhere('d.statut = :statut')
            ->setParameter('benevole', $benevole)
            ->setParameter('tache', $tache)
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter les demandes en attente
     */
    public function countEnAttente(): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
