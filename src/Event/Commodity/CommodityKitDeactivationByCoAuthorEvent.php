<?php

namespace App\Event\Commodity;

use App\Entity\Market\CommodityKit;
use App\Entity\User;

class CommodityKitDeactivationByCoAuthorEvent
{
    private CommodityKit $commodity;
    private ?User $deactivationInitiator;

    /**
     * @return CommodityKit
     */
    public function getCommodity(): CommodityKit
    {
        return $this->commodity;
    }

    /**
     * @param CommodityKit $commodity
     */
    public function setCommodity(CommodityKit $commodity): void
    {
        $this->commodity = $commodity;
    }

    /**
     * @return User|null
     */
    public function getDeactivationInitiator(): ?User
    {
        return $this->deactivationInitiator;
    }

    /**
     * @param User|null $deactivationInitiator
     */
    public function setDeactivationInitiator(?User $deactivationInitiator): void
    {
        $this->deactivationInitiator = $deactivationInitiator;
    }
}