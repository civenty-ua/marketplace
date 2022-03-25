<?php

namespace App\Service;

use App\Entity\DeadUrl;
use Doctrine\ORM\EntityManagerInterface;

class DeadUrlService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function validateDeadUrlGroupFilter(DeadUrl $deadUrl): bool
    {
        $purgedDeadRequest = $this->purgeDeadRequest($deadUrl);

        return str_ends_with($purgedDeadRequest, '*');
    }


    public function parseAndDeleteSimilarDeadUrl(DeadUrl $entity)
    {
        $startsWith = $this->getDeadUrlDeleteFilter($entity);

        $q = $this->em->getRepository(DeadUrl::class)->createQueryBuilder('du');
        $q->delete()->where($q->expr()->like('du.deadRequest', $startsWith))->getQuery()->execute();
    }

    private function purgeDeadRequest(DeadUrl $deadUrl)
    {
        return str_replace(' ', '', $deadUrl->getDeadRequest());
    }

    private function getDeadUrlDeleteFilter(DeadUrl $entity)
    {
        $purgedSlashes = addcslashes($this->purgeDeadRequest($entity), '/');

        return '\'' . str_replace('*', '', $purgedSlashes) . '%\'';
    }
}