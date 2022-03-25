<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Market\Commodity;
use App\Entity\Market\UserProperty;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DoctrineSubscriber
{
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function addUserProperty(object $entity): void
    {
        if (!($entity instanceof User) || $entity->getUserProperty()) {
            return;
        }

        $entity->setUserProperty(new UserProperty());
        $this->entityManager->persist($entity);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->addUserProperty($entity);

        $this->proceedSlug($entity);

        $this->entityManager->flush();
    }
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->proceedSlug($entity);
    }

    private function proceedSlug(object $entity){
        if($entity instanceof Commodity)
        {
            $entity->setSlug($entity->getTitle().' '.$entity->getId());
        }
    }
}