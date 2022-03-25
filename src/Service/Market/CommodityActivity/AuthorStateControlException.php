<?php
declare(strict_types=1);

namespace App\Service\Market\CommodityActivity;

use Exception;
/**
 * Market, commodity activity management.
 *
 * Says, that commodity author can`t handle commodity as published
 * Possible reasons:
 *  - banned
 *  - not registered/lost his registration
 *  - not enough roles/groups
 *  - etc
 */
class AuthorStateControlException extends Exception
{

}