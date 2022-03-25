<?php
declare(strict_types=1);

namespace App\Service\Market;

use DateTime;
use UnexpectedValueException;
use App\Entity\Market\{
    Commodity,
    CommodityKit,
    UserProperty,
};
/**
 * Market commodity active date calculator.
 *
 * @package App\Service\Market
 */
class CommodityDatesCalculator
{
    /**
     * Get commodity active to date.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  DateTime                    Active to date.
     * @throws  UnexpectedValueException    Process failed.
     */
    public function getCommodityActiveToDate(Commodity $commodity): DateTime
    {
        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            return $this->calculateKitActiveToDate($commodity);
        }

        return $this->calculateCommodityActiveToDate($commodity);
    }
    /**
     * Calculate and get commodities "active to" date.
     *
     * @param   Commodity $commodity    Commodity.
     *
     * @return  DateTime                Active to date.
     */
    private function calculateCommodityActiveToDate(Commodity $commodity): DateTime
    {
        $user = $commodity->getUser();

        if (!$user) {
            throw new UnexpectedValueException("commodity {$commodity->getTitle()} ".
                'has no user to calculate active to date');
        }

        $activityDurationValue  = $user->getUserProperty()
            ? $user->getUserProperty()->getCommodityActiveToExtendedByDays()
            : 0;
        $activityDuration       = $activityDurationValue > 0
            ? $activityDurationValue
            : UserProperty::COMMODITY_DAYS_ACTIVITY_DEFAULT;

        return (new DateTime('now'))->modify("+$activityDuration days");
    }
    /**
     * Calculate and get kit "active to" date.
     *
     * @param   CommodityKit $kit   Kit.
     *
     * @return  DateTime            Active to date.
     */
    private function calculateKitActiveToDate(CommodityKit $kit): DateTime
    {
        $datesSet = [];

        foreach ($kit->getCommodities() as $commodity) {
            $datesSet[$commodity->getActiveTo()->getTimestamp()] = $commodity->getActiveTo();
        }

        if (count($datesSet) === 0) {
            throw new UnexpectedValueException("kit {$kit->getTitle()} ".
                'has no inner commodities to calculate active to date');
        }

        $minimumDateStamp = min(array_keys($datesSet));

        return $datesSet[$minimumDateStamp];
    }
}
