<?php

namespace App\EventSubscriber;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\DeadUrl;
use App\Entity\Expert;
use App\Entity\Item;
use App\Entity\ItemRegistration;
use App\Entity\Market\RequestRole;
use App\Entity\Page;
use App\Entity\Partner;
use App\Entity\Tags;
use App\Entity\User;
use App\Service\Notification\NotificationSenderService;
use App\Service\Notification\SystemNotificationSender;
use Cocur\Slugify\Slugify;
use Composer\DependencyResolver\Request;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\{
    BeforeEntityPersistedEvent,
    BeforeEntityUpdatedEvent,
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class EasyAdminSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private SystemNotificationSender $systemNotificationSender;

    public function __construct(
        EntityManagerInterface       $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        SystemNotificationSender     $systemNotificationSender
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->systemNotificationSender = $systemNotificationSender;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['addUser'],
            BeforeEntityPersistedEvent::class => ['persistSlug'],
            BeforeEntityUpdatedEvent::class => ['updateSlug'],
        ];
    }

    public function addUser(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }
        $this->setPassword($entity);
    }

    /**
     * @param User $entity
     */
    public function setPassword(User $entity): void
    {
        $pass = $entity->getPassword();

        $entity->setPassword(
            $this->passwordEncoder->encodePassword(
                $entity,
                $pass
            )
        );
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function persistSlug(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        $this->setSlug($entity);
        $this->addDeadUrl($entity);
        $this->setItemTypeToItemRegistration($entity);
    }

    public function updateSlug(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();
        $this->setSlug($entity);
        $this->deleteUserRoleRequest($entity);
    }

    /**
     * @throws \ReflectionException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    private function setSlug($entity)
    {
        if ($this->checkEntityDoesntContainSlugField($entity)) {
            return;
        }

        if ($this->checkEntityContainsSlugFieldAndNotContainAliasField($entity)) {
            if (!$entity->getSlug()) {
                $slugify = new Slugify();
                if ($this->checkEntityShouldSlugifyByNameField($entity)) {
                    $slug = $slugify->slugify($entity->getTranslations()['uk']->getName());
                } else {
                    $slug = $slugify->slugify($entity->getTranslations()['uk']->getTitle());
                }

                $uniqueSlug = $this->makeUniqueSlug($entity, $slug);

                $entity->setSlug($uniqueSlug);
            }
        } else {
            if (!$entity->getAlias()) {
                $slugify = new Slugify();
                $slug = $slugify->slugify($entity->getTranslations()['uk']->getTitle());

                $uniqueSlug = $this->makeUniqueSlug($entity, $slug);

                $entity->setAlias($uniqueSlug);
            }
        }
    }

    /**
     * @param $entity
     * @param $slug
     *
     * @return string
     * @throws \Doctrine\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    protected function makeUniqueSlug($entity, $slug): string
    {
        $entityName = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
        if (!is_subclass_of($entity, 'App\Entity\Item')) {
            $slugCount = count($this->entityManager->getRepository($entityName)->findAllBySlug($slug));
        } else {
            $slugCount = count($this->entityManager->getRepository(Item::class)->findAllBySlug($slug));
        }
        return ($slugCount > 0) ? ($slug . '-' . $slugCount) : $slug;
    }

    public function addDeadUrl($entity)
    {
        if (!($entity instanceof DeadUrl)) {
            return;
        }

        $entity->setCreatedAt();
        $checkSum = crc32($entity->getDeadRequest());
        $checkSum = current(unpack('l', pack('l', $checkSum)));
        $entity->setCheckSum($checkSum);
    }

    public function checkEntityDoesntContainSlugField($entity): bool
    {
        return (
            !is_subclass_of($entity, 'App\Entity\Item')
            && !($entity instanceof Page)
            && !($entity instanceof Tags)
            && !($entity instanceof Category)
            && !($entity instanceof Partner)
            && !($entity instanceof Expert)
        );
    }

    public function checkEntityShouldSlugifyByNameField($entity): bool
    {
        return ($entity instanceof Tags
            || $entity instanceof Category
            || $entity instanceof Partner
            || $entity instanceof Expert
        );
    }

    public function checkEntityContainsSlugFieldAndNotContainAliasField($entity): bool
    {
        return (is_subclass_of($entity, 'App\Entity\Item')
            || $entity instanceof Tags
            || $entity instanceof Category
            || $entity instanceof Partner
            || $entity instanceof Expert
        );
    }

    public function setItemTypeToItemRegistration($entity)
    {
        if (!($entity instanceof ItemRegistration)) {
            return;
        }
        $entity->setItemType();
    }

    public function deleteUserRoleRequest($entity)
    {
        if (!($entity instanceof User)) {
            return;
        }

        if (!empty($entity->getRequestRoles())) {
            foreach (User::$rolesInRequestRoles as $key => $role) {
                if (!in_array($role, $entity->getRoles()) && $entity->getRequestRole($key)) {
                    foreach ($entity->getRequestRoles() as $requestRole) {
                        if ($requestRole->getRole() === $key) {
                            $entity->removeRequestRole($requestRole);
                            $this->entityManager->remove($requestRole);
                            $this->systemNotificationSender->sendSingleNotification([
                                'receiver' => $entity,
                                'message' => "Вашу роль {$entity->getNameRoles()[$role]} було деактивовано."
                            ]);
                        }
                    }
                }
            }
        }

    }
}
