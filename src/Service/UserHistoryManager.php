<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Item;
use App\Entity\ItemRegistration;
use App\Entity\Occurrence;
use App\Entity\UserDownload;
use App\Entity\UserViewed;
use App\Entity\Webinar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Security\Core\User\UserInterface;

class UserHistoryManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function viewWebinar(UserInterface $user, Webinar $webinar)
    {
        $this->viewItem($user, $webinar);
    }

    public function viewCourse(UserInterface $user, Course $course)
    {
        $this->viewItem($user, $course);
    }

    public function viewItem(UserInterface $user, Item $item)
    {
        $viewedItem = $this->entityManager
            ->getRepository(UserViewed::class)->findOneBy([
                'user' => $user,
                'item' => $item
            ]);

        if (!$viewedItem) {
            $viewedItem = new UserViewed();
            $viewedItem->setUser($user);
            $viewedItem->setItem($item);
            $this->fakeViewWebinarOrOccurrence($item);
        }

        $viewedItem->updateViewedAt();

        $this->entityManager->persist($viewedItem);
        $this->entityManager->flush();
    }
    public function fakeViewWebinarOrOccurrence(Item $item){
        if(!($item instanceof Webinar || $item instanceof Occurrence)){
            return;
        }
        $item->increaseViewsAmount();
    }

    public function saveFile(UserInterface $user, string $link, string $text = null)
    {
        $ext = pathinfo($link)['extension'];
        $fileName = basename($link);

        $fileDownload = $this->entityManager
            ->getRepository(UserDownload::class)->findOneBy([
                'user' => $user,
                'link' => $link
            ]);

        if (!$fileDownload) {
            $fileDownload = new UserDownload();
            $fileDownload->setUser($user);
            $fileDownload->setExt($ext);
            $fileDownload->setFileName($fileName);
            $fileDownload->setLink($link);
            $fileDownload->setTitle(urldecode($fileName));
        }

        $fileDownload->updateDownloadedAt();

        $this->entityManager->persist($fileDownload);
        $this->entityManager->flush();

        return true;
    }

    public function getRegisteredUser(Item $item, $user)
    {
        if (is_null($user)) {
            return null;
        }

        return $this->entityManager->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $item->getId()]);
    }

    public function isShownSuccessRegisteredMessageBlock(Item $item, UserInterface $user): ?bool
    {
        if ($this->getRegisteredUser($item, $user) && $this->isItemExpected($item)) {
            return true;
        }
        if ($this->getRegisteredUser($item, $user) && !$this->isItemExpected($item)) {
            return false;
        }
        return null;
    }

    public function isItemExpected(Item $item): bool
    {
        return $item->getStartDate() > new \DateTime('now');
    }

    public function registerButtonShow(Item $item, $user)
    {
        if(!($item instanceof Webinar || $item instanceof Occurrence)){
            return;
        }
        if (!$user && $item->getRegistrationRequired()) {
            return $registered = false;
        } elseif (!$user && !$item->getRegistrationRequired()
            && ($item->getStartDate() < new \DateTime('now')
                || !$item->getStartDate())) {
            $registered = true;
        } else {
            $registered = false;
        }
        if ($item->getRegistrationRequired()) {
            $registeredUser = $this->getRegisteredUser($item, $user);
            !is_null($registeredUser) ? $registered = true : $registered = false;
        } elseif ($item->getStartDate() > new \DateTime('now')) {
            $registeredUser = $this->getRegisteredUser($item, $user);
            !is_null($registeredUser) ? $registered = true : $registered = false;
        } else {
            $registered = true;
        }

        return $registered;
    }
}
