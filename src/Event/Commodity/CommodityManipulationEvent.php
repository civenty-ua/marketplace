<?php
declare(strict_types=1);

namespace App\Event\Commodity;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\{
    User,
    Market\Commodity,
};
/**
 * Any manipulations with commodity basic event class.
 */
abstract class CommodityManipulationEvent extends Event
{
    private User        $user;
    private Commodity   $commodity;

    public function __construct(User $user, Commodity $commodity)
    {
        $this->user         = $user;
        $this->commodity    = $commodity;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
