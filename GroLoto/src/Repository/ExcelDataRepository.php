<?php

namespace App\Repository;

use App\Entity\ExcelData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExcelDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcelData::class);
    }

    public function save(ExcelData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExcelData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllSheets(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.imported_at', 'DESC')
            ->addOrderBy('e.sheet_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySheetName(string $sheetName): ?ExcelData
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.sheet_name = :name')
            ->setParameter('name', $sheetName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
