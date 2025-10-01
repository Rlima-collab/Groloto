<?php

namespace App\Repository;

use App\Entity\Benevole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Benevole>
 *
 * @method Benevole|null find($id, $lockMode = null, $lockVersion = null)
 * @method Benevole|null findOneBy(array $criteria, array $orderBy = null)
 * @method Benevole[]    findAll()
 * @method Benevole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BenevoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Benevole::class);
    }

    /**
     * Récupère tous les bénévoles actifs avec leurs informations utilisateur
     * @return Benevole[] Returns an array of Benevole objects
     */
    public function findActiveWithUser(): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.utilisateur', 'u')
            ->addSelect('u')
            ->where('b.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de bénévoles actifs
     */
    public function countActive(): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.actif = :actif')
            ->setParameter('actif', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les bénévoles récemment inscrits (ce mois-ci)
     */
    public function findRecentlyRegistered(): array
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        
        return $this->createQueryBuilder('b')
            ->innerJoin('b.utilisateur', 'u')
            ->addSelect('u')
            ->where('b.actif = :actif')
            ->andWhere('u.date_creation >= :startOfMonth')
            ->setParameter('actif', true)
            ->setParameter('startOfMonth', $startOfMonth)
            ->orderBy('u.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}