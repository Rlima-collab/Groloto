<?php

namespace App\Repository;

use App\Entity\DisponibiliteWeekend;
use App\Entity\Benevole;
use App\Entity\Weekend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DisponibiliteWeekend>
 */
class DisponibiliteWeekendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisponibiliteWeekend::class);
    }

    /**
     * Trouve toutes les disponibilités d'un bénévole pour un weekend
     * @return DisponibiliteWeekend[]
     */
    public function findByBenevoleAndWeekend(Benevole $benevole, Weekend $weekend): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.benevole = :benevole')
            ->andWhere('d.weekend = :weekend')
            ->setParameter('benevole', $benevole)
            ->setParameter('weekend', $weekend)
            ->orderBy('d.jour', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les disponibilités pour un weekend
     * @return DisponibiliteWeekend[]
     */
    public function findByWeekend(Weekend $weekend): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.benevole', 'b')
            ->join('b.utilisateur', 'u')
            ->where('d.weekend = :weekend')
            ->setParameter('weekend', $weekend)
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('d.jour', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les bénévoles disponibles pour un créneau spécifique
     * @return DisponibiliteWeekend[]
     */
    public function findDisponiblesForCreneau(Weekend $weekend, \DateTimeInterface $jour, string $creneau): array
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.benevole', 'b')
            ->where('d.weekend = :weekend')
            ->andWhere('d.jour = :jour')
            ->setParameter('weekend', $weekend)
            ->setParameter('jour', $jour);

        switch ($creneau) {
            case 'matin':
                $qb->andWhere('d.matin = 1');
                break;
            case 'apres_midi':
                $qb->andWhere('d.apresMidi = 1');
                break;
            case 'soir':
                $qb->andWhere('d.soir = 1');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Vérifie si un bénévole a déjà renseigné ses disponibilités pour un weekend
     */
    public function hasDisponibilites(Benevole $benevole, Weekend $weekend): bool
    {
        $count = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.benevole = :benevole')
            ->andWhere('d.weekend = :weekend')
            ->setParameter('benevole', $benevole)
            ->setParameter('weekend', $weekend)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Supprime toutes les disponibilités d'un bénévole pour un weekend
     */
    public function deleteByBenevoleAndWeekend(Benevole $benevole, Weekend $weekend): int
    {
        return $this->createQueryBuilder('d')
            ->delete()
            ->where('d.benevole = :benevole')
            ->andWhere('d.weekend = :weekend')
            ->setParameter('benevole', $benevole)
            ->setParameter('weekend', $weekend)
            ->getQuery()
            ->execute();
    }

    /**
     * Compte le nombre de bénévoles ayant renseigné leurs disponibilités pour un weekend
     */
    public function countBenevolesWithDisponibilites(Weekend $weekend): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(DISTINCT d.benevole)')
            ->where('d.weekend = :weekend')
            ->setParameter('weekend', $weekend)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
