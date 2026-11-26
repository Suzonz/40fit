<?php

namespace App\Repository;

use App\Entity\Challenge;
use App\Entity\ChallengeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    /**
     * Les défis d'une catégorie (ou tous si $category est null),
     * rangés par catégorie puis dans l'ordre du fichier.
     *
     * @return Challenge[]
     */
    public function findByCategory(?ChallengeCategory $category = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('cat')
            ->join('c.category', 'cat')
            ->orderBy('cat.id', 'ASC')
            ->addOrderBy('c.id', 'ASC');

        if ($category !== null) {
            $qb->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}
