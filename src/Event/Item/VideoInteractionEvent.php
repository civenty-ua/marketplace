<?php

namespace App\Event\Item;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\VideoItem;

class VideoInteractionEvent extends Event
{
    private VideoItem   $video;
    private int         $watchDuration;
    /**
     * Constructor.
     *
     * @param   VideoItem   $video          Video.
     * @param   int         $watchDuration  Watch duration.
     */
    public function __construct(VideoItem $video, int $watchDuration)
    {
        $this->video            = $video;
        $this->watchDuration    = $watchDuration;
    }
    /**
     * Get video.
     *
     * @return VideoItem                    Video entity.
     */
    public function getVideo(): VideoItem
    {
        return $this->video;
    }
    /**
     * Get watch duration.
     *
     * @return int                          Watch duration.
     */
    public function getWatchDuration(): int
    {
        return $this->watchDuration;
    }
}