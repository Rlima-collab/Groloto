<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un rôle par son nom
     */
    public function findOneByNom(string $nom): ?Role
    {
        return $this->findOneBy(['nom' => $nom]);
    }

    /**
     * Récupère les rôles disponibles pour l'inscription publique
     */
    public function findPublicRoles(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nom IN (:roles)')
            ->setParameter('roles', ['benevole', 'mecene']) // Exclure admin
            ->orderBy('r.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}