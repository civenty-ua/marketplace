<?php


namespace App\Event;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\DeadUrl;
use App\Service\DeadUrlBulkService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use App\Repository\DeadUrlRepository;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class RequestListener
{

    private DeadUrlBulkService $deadUrlBulkService;
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $em,
                                DeadUrlBulkService $deadUrlBulkService,
                                TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->deadUrlBulkService = $deadUrlBulkService;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $this->invalidateBannedUser($event);

        $uri = $event->getRequest()->getRequestUri();
        $deadUrl = $this->deadUrlBulkService->getDeadUrlBulkOrNonActiveBulkCounterByUri($uri);

        if ($deadUrl instanceof DeadUrl
            && $deadUrl->getRedirectTo()
            && $deadUrl->getRedirectTo() != $uri) {
            $this->em->flush();

            $response = new RedirectResponse($deadUrl->getRedirectTo(), 301);
            $event->setResponse($response);
        }
        $checkSum = crc32($uri);
        $checkSum = current(unpack('l', pack('l', $checkSum)));
        $controller = $event->getRequest()->attributes->get('_controller');

        try {
            if ($controller && $controller == 'error_controller') {
                throw new RouteNotFoundException();
            }
        } catch (RouteNotFoundException $e) {
            if (!$deadUrl || is_int($deadUrl)) {
                $deadUrl = $this->em->getRepository(DeadUrl::class)->findOneBy([
                    'checkSum' => $checkSum,
                    'deadRequest' => $uri
                ]);

                if ($deadUrl) {
                    $deadUrl->increaseAttemptAmount();
                    $this->em->persist($deadUrl);
                }
                if ($deadUrl && $deadUrl->getIsActive() && $deadUrl->getRedirectTo()) {
                    $response = new RedirectResponse($deadUrl->getRedirectTo(), 301);
                    $event->setResponse($response);
                }
                if (is_null($deadUrl) && !str_contains($uri, 'admin?crudAction')) {
                    $deadUrl = new DeadUrl();
                    $deadUrl->setDeadRequest($uri);
                    $deadUrl->setCheckSum($checkSum);
                    $deadUrl->setIsActive(false);
                    $deadUrl->setCreatedAt();
                    $deadUrl->increaseAttemptAmount();
                    $this->em->persist($deadUrl);
                }
                $this->em->flush();
            }
        }
    }

    private function invalidateBannedUser(RequestEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return;
        }

        if (!($token instanceof AnonymousToken) && $token->getUser()->getIsBanned() === true) {
            $this->tokenStorage->setToken();
            $request->getSession()->invalidate();
            return;
        }
    }
}