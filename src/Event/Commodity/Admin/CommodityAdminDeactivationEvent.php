<?php

namespace App\Event\Commodity\Admin;

use App\Entity\Market\Commodity;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CommodityAdminDeactivationEvent extends Event
{
    private Commodity $commodity;

    private User $admin;

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

    /**
     * @return User
     */
    public function getAdmin(): User
    {
        return $this->admin;
    }

    /**
     * @param User $admin
     */
    public function setAdmin(User $admin): void
    {
        $this->admin = $admin;
    }
}