<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\UserItemFeedback;
/**
 * @method UserItemFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserItemFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserItemFeedback[]    findAll()
 * @method UserItemFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserItemFeedbackRepository extends ServiceEntityRepository
{
    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 6;
        parent::__construct($registry, UserItemFeedback::class);
    }

    public function countItemRating($id)
    {
        return $this->createQueryBuilder('u')
            ->select('avg (u.rate) as rate')
            ->addSelect('count(distinct u.user) as user_count')
            ->where('u.item = :item')
            ->addGroupBy('u.item')
            ->setParameter('item', $id)
            ->getQuery()->getOneOrNullResult();
    }

    public function getTopUserFeedBack($item, $form)
    {
        $userFeedBack = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.item = :item')
            ->andWhere('u.feedbackForm = :form')
            ->setParameters([
                'item' => $item,
                'form' => $form,
            ])
            ->setMaxResults($this->maxCount)
            ->getQuery()->getResult();
        if (!empty($userFeedBack)) {
            foreach ($userFeedBack as $uFeedBack) {
                $userFeedBackIds[] = $uFeedBack['id'];
            }
        }
        return empty($userFeedBackIds) ? [] : $userFeedBackIds;
    }

    public function getUserFeedBackForEstimate($item,$form)
    {
        $userFeedBack = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.item = :item')
            ->andWhere('u.feedbackForm = :form')
            ->setParameters([
                'item' => $item,
                'form' => $form,
            ])
            ->getQuery()->getResult();
        if (!empty($userFeedBack)) {
            foreach ($userFeedBack as $uFeedBack) {
                $userFeedBackIds[] = $uFeedBack['id'];
            }
        }
        return empty($userFeedBackIds) ? [] : $userFeedBackIds;
    }
}
