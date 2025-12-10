<?php

namespace App\Repository;

use App\Entity\DemandeAnnulation;
use App\Entity\Benevole;
use App\Entity\AffectationTache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeAnnulation>
 */
class DemandeAnnulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeAnnulation::class);
    }

    /**
     * Trouver toutes les demandes en attente
     */
    public function findEnAttente(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('d.dateDemande', 'DESC')
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
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifier s'il existe déjà une demande en attente pour une affectation
     */
    public function findByAffectationEnAttente(AffectationTache $affectation): ?DemandeAnnulation
    {
        return $this->createQueryBuilder('d')
            ->where('d.affectation = :affectation')
            ->andWhere('d.statut = :statut')
            ->setParameter('affectation', $affectation)
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
