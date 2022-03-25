<?php

namespace App\Repository;

use App\Entity\FeedbackFormQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedbackFormQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackFormQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackFormQuestion[]    findAll()
 * @method FeedbackFormQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackFormQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackFormQuestion::class);
    }

    // /**
    //  * @return FeedbackFormQuestion[] Returns an array of FeedbackFormQuestion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findQuestionsToDisplay($value)
    {
        $questions = $this->createQueryBuilder('f')
            ->select('f.id')
            ->andWhere('f.feedbackForm = :val')
            ->setParameter('val', $value)
            ->andWhere('f.type = :type')
            ->setParameter('type', 'review')
            ->andWhere('f.required = true')
            ->getQuery()
            ->getResult();

        if (!empty($questions)) {
            foreach ($questions as $question) {
                $questionIds[] = $question['id'];
            }
        }
        return empty($questionIds) ? [] : $questionIds;
    }

    public function findRatingQuestions($value)
    {
        $questions = $this->createQueryBuilder('f')
            ->select('f.id')
            ->andWhere('f.feedbackForm = :val')
            ->setParameter('val', $value)
            ->andWhere('f.type = :type')
            ->setParameter('type', 'rate')
            ->getQuery()
            ->getResult();

        if (!empty($questions)) {
            foreach ($questions as $question) {
                $questionIds[] = $question['id'];
            }
        }
        return empty($questionIds) ? [] : $questionIds;
    }

}
