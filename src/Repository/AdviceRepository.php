<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    public function getAdvicesForCurrentMonth(): array
    {
        $date = new \DateTime();
        $month = (int) $date->format('m');
        $year = (int) $date->format('Y');

        return $this->createQueryBuilder('a')
            ->where('MONTH(a.date) = :month')
            ->andWhere('YEAR(a.date) = :year')
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();
    }
}
