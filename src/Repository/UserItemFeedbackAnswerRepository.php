<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\UserItemFeedbackAnswer;
/**
 * @method UserItemFeedbackAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserItemFeedbackAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserItemFeedbackAnswer[]    findAll()
 * @method UserItemFeedbackAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserItemFeedbackAnswerRepository extends ServiceEntityRepository
{
    private int $maxCount = 6;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 6;
        parent::__construct($registry, UserItemFeedbackAnswer::class);
    }

    public function findAnswers($value, $userFeedBack)
    {
        return $this->createQueryBuilder('ufa')
            ->join('ufa.userFeedback', 'u')
            ->where('ufa.feedbackFormQuestion IN (:val)')
            ->setParameter('val', $value)
            ->andWhere('ufa.isActive = true')
            ->andWhere('u IN (:userFeedback)')
            ->setParameter('userFeedback', $userFeedBack)
            ->getQuery()
            ->setMaxResults($this->maxCount)
            ->getResult();
    }

    public function countRating($value,$userFeedBack)
    {
        return $this->createQueryBuilder('ufa')
            ->select('AVG(ufa.answer)')
            ->join('ufa.userFeedback', 'u')
            ->where('ufa.feedbackFormQuestion IN (:val)')
            ->setParameter('val', $value)
            ->andWhere('u IN (:userFeedback)')
            ->setParameter('userFeedback', $userFeedBack)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
