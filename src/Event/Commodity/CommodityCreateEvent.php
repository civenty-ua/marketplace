<?php

namespace App\Event\Commodity;

use App\Entity\Market\Commodity;
use Symfony\Contracts\EventDispatcher\Event;

class CommodityCreateEvent extends Event
{
    private Commodity $commodity;

    /**
     * @return Commodity
     */
    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    /**
     * @param Commodity $commodity
     */
    public function setCommodity(Commodity $commodity): void
    {
        $this->commodity = $commodity;
    }
}