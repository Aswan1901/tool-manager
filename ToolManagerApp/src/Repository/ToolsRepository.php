<?php

namespace App\Repository;

use App\Entity\Categories;
use App\Entity\Tools;
use App\Enums\DepartmentType;
use App\Enums\ToolStatusType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tools>
 */
class ToolsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Private EntityManagerInterface $entityManager,)
    {
        parent::__construct($registry, Tools::class);
    }

    public function findByFilters(?string $department, ?string $status, ?string $category, ?string $minCost, ?string $maxCost): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($department) {
            $qb->andWhere('t.ownerDepartment = :department')
                ->setParameter('department', $department);
        }

        if ($status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if ($category) {
            $categoryEntity = $this->entityManager->getRepository(Categories::class)->findOneBy
            (
                ['name' => $category]
            );

            if (!$categoryEntity) {
                throw new \InvalidArgumentException("Category $category does not exist");
            }

            $qb->andWhere('t.category = :Category')
                ->setParameter('Category', $categoryEntity);
        }

        if ($minCost) {
            $qb->andWhere('t.monthly_cost >= :minCost')
                ->setParameter('minCost', $minCost);
        }

        if ($maxCost) {
            $qb->andWhere('t.monthly_cost <= :maxCost')
                ->setParameter('maxCost', $maxCost);
        }

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return Tools[] Returns an array of Tools objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tools
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
