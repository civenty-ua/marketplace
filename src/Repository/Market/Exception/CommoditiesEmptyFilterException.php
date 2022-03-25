<?php
declare(strict_types = 1);

namespace App\Repository\Market\Exception;

use Exception;
/**
 * Income commodities filter is empty and no query is required.
 */
class CommoditiesEmptyFilterException extends Exception
{

}