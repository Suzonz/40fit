<?php

namespace App\Repository;

use App\Entity\Challenge;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evaluation>
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    /**
     * Tous les résultats d'un utilisateur, avec leur défi et sa catégorie,
     * du plus récent au plus ancien (tableau de bord).
     *
     * @return Evaluation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('c', 'cat')
            ->join('e.challenge', 'c')
            ->join('c.category', 'cat')
            ->andWhere('e.associatedUser = :user')
            ->setParameter('user', $user)
            ->orderBy('e.createdAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Les résultats d'un utilisateur à un défi, du plus récent au plus ancien.
     *
     * @return Evaluation[]
     */
    public function findByUserAndChallenge(User $user, Challenge $challenge): array
    {
        return $this->findBy(
            ['associatedUser' => $user, 'challenge' => $challenge],
            ['createdAt' => 'DESC', 'id' => 'DESC']
        );
    }
}
