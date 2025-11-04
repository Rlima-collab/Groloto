<?php
namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    public function findAllOrdered(string $order = 'DESC'): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', $order)
            ->getQuery()
            ->getResult();
    }

    public function countUnread(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.lu = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
}