<?php
declare(strict_types=1);

namespace App\Service\Market\CommodityActivity;

use Exception;
/**
 * Market, commodity activity management.
 *
 * Says, that commodity is kit, and it has not published included commodities.
 */
class KitHasUnpublishedPartsException extends Exception
{

}