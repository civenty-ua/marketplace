<?php
declare(strict_types=1);

namespace App\Service\Market\CommodityActivity;

use Exception;
/**
 * Market, commodity activity management.
 *
 * Says, that user has not enough publications to handle one more.
 */
class PublicationsCountControlException extends Exception
{

}