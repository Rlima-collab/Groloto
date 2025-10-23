<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    /**
     * Trouve toutes les tâches avec leurs événements
     */
    public function findAllWithEvenement(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.evenement', 'e')
            ->addSelect('e')
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches par événement
     */
    public function findByEvenement($evenementId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.evenement = :evenementId')
            ->setParameter('evenementId', $evenementId)
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches futures
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

    // Ajoutez cette méthode pour corriger l'erreur dans votre contrôleur
    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.evenement', 'e')
            ->addSelect('e')
            ->orderBy('t.debut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}