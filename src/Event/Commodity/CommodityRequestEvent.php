<?php
declare(strict_types=1);

namespace App\Event\Commodity;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Market\Commodity;
/**
 * Commodity request event.
 */
class CommodityRequestEvent extends Event
{
    private Commodity $commodity;

    public function __construct(Commodity $commodity)
    {
        $this->commodity = $commodity;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
