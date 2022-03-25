<?php

namespace App\Controller;

use Throwable;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Event\Item\VideoInteractionEvent;
use App\Entity\VideoItem;

class VideoController extends AbstractController
{
    protected EventDispatcherInterface  $eventDispatcher;
    protected HttpKernelInterface       $kernel;

    public function __construct(
        EventDispatcherInterface    $eventDispatcher,
        HttpKernelInterface         $kernel
    ) {
        $this->eventDispatcher  = $eventDispatcher;
        $this->kernel           = $kernel;
    }
    /**
     * @Route("/video/{id}/interaction/log", name="video_interaction_log")
     */
    public function logInteraction(Request $request, int $id): Response
    {
        try {
            /** @var VideoItem|null $video */
            $video = $this
                ->getDoctrine()
                ->getRepository(VideoItem::class)
                ->find($id);
            if (!$video) {
                return new JsonResponse([
                    'message' => "Video $id was not found",
                ], 404);
            }

            $watchDuration = (int) ($request->toArray()['duration'] ?? 0);
            if ($watchDuration < 0) {
                return new JsonResponse([
                    'message' => 'watched duration can not be negative',
                ], 400);
            }

            $event = new VideoInteractionEvent($video, $watchDuration);
            $this->eventDispatcher->dispatch($event);

            return new JsonResponse([
                'message' => 'success',
            ], 200);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'message' => "server error: {$exception->getMessage()}",
            ], 500);
        }
    }
}
