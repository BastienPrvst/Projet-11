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
        $now = new \DateTimeImmutable('first day of this month 00:00:00');
        $end = $now->modify('first day of next month');

        return $this->createQueryBuilder('a')
            ->where('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('start', $now)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getAdvicesForSelectedMonth(int $mois)
    {
        $start = new \DateTime();
        $start->setDate($start->format('Y'), $mois, $start->format('d'));
        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->modify('first day of next month');
        $end->setTime(00, 00, 00);

        return $this->createQueryBuilder('a')
            ->where('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getScalarResult();
    }
}
