<?php
declare(strict_types = 1);

namespace App\Twig\Market;

use Throwable;
use Twig\{
    TwigFilter,
    Extension\AbstractExtension,
};
use App\Service\Market\CommodityActivity\{
    ActivityTurnedOffException,
    ActivityDatesControlException,
    AuthorStateControlException,
    KitHasUnpublishedPartsException,
    KitNotApprovedException,
    CommodityActivityManager,
};
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityKit,
};
/**
 * Market, commodity activity information provider.
 *
 * @package App\Twig
 */
class CommodityActivityExtension extends AbstractExtension
{
    private CommodityActivityManager $commodityActivityManager;

    public function __construct(CommodityActivityManager $commodityActivityManager)
    {
        $this->commodityActivityManager = $commodityActivityManager;
    }
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('checkCommodityIsPublished',         [$this, 'checkCommodityIsPublished']),
            new TwigFilter('getCommodityInactivityReason',      [$this, 'getCommodityInactivityReason']),
            new TwigFilter('checkCommodityCanBeActivated',      [$this, 'checkCommodityCanBeActivated']),
            new TwigFilter('checkCommodityCanBeDeactivated',    [$this, 'checkCommodityCanBeDeactivated']),
            new TwigFilter('checkKitCanBeLeft',                 [$this, 'checkKitCanBeLeft']),
        ]);
    }
    /**
     * Check if commodity is published.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  bool                        Commodity is published.
     */
    public function checkCommodityIsPublished(Commodity $commodity): bool
    {
        try {
            $this->commodityActivityManager->checkCommodityIsPublished($commodity);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
    /**
     * Get commodity inactivity reason, string unique code. Null, if no.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  string|null                 Commodity inactivity reason,
     *                                      or bull, if there is no reason.
     */
    public function getCommodityInactivityReason(Commodity $commodity): ?string
    {
        try {
            $this->commodityActivityManager->checkCommodityIsPublished($commodity);
            return null;
        } catch (KitNotApprovedException $exception) {
            return 'kitNotApproved';
        } catch (KitHasUnpublishedPartsException $exception) {
            return 'kitHasInactiveCommodities';
        } catch (AuthorStateControlException $exception) {
            return 'commodityHasInactiveOwner';
        } catch (ActivityTurnedOffException | ActivityDatesControlException $exception) {
            return 'commodityNotPublished';
        }
    }
    /**
     * Check if commodity can be published by current user.
     *
     * @param   Commodity   $commodity      Commodity.
     * @param   User        $user           User.
     *
     * @return  bool                        Commodity can be published.
     */
    public function checkCommodityCanBeActivated(Commodity $commodity, User $user): bool
    {
        if ($this->checkCommodityIsPublished($commodity)) {
            return false;
        }
        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            if (!$commodity->getIsApproved()) {
                return false;
            }
            try {
                $this->commodityActivityManager->checkKitCanBeApproved($commodity, $user);
            } catch (Throwable $exception) {
                return false;
            }
        }

        try {
            $this->commodityActivityManager->checkCommodityCanBeActivated($commodity, $user);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
    /**
     * Check if commodity can be published by current user.
     *
     * @param   Commodity   $commodity      Commodity.
     * @param   User        $user           User.
     *
     * @return  bool                        Commodity can be published.
     */
    public function checkCommodityCanBeDeactivated(Commodity $commodity, User $user): bool
    {
        if (!$this->checkCommodityIsPublished($commodity)) {
            return false;
        }
        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            if (!$commodity->getIsApproved()) {
                return false;
            }
        }

        try {
            $this->commodityActivityManager->checkCommodityCanBeDeactivated($commodity, $user);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
    /**
     * Check if commodity can be published by current user.
     *
     * @param   CommodityKit    $kit        Kit.
     * @param   User            $user       User.
     *
     * @return  bool                        Commodity can be published.
     */
    public function checkKitCanBeLeft(CommodityKit $kit, User $user): bool
    {
        try {
            $this->commodityActivityManager->checkKitCanBeLeft($kit, $user);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
}
