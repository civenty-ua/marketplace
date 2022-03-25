<?php
declare(strict_types=1);

namespace App\Service\Market\CommodityActivity;

use Exception;
/**
 * Market, commodity activity management.
 *
 * Says, that commodity activity state changed can not be done, because access denied for such operation.
 */
class ActivityChangeAccessDeniedException extends Exception
{

}