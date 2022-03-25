<?php
declare(strict_types=1);

namespace App\Service\Market;

use App\Entity\{
    User,
    Market\CommodityProduct,
};
/**
 * Market product allowed type`s provider.
 *
 * @package App\Service\Market
 */
class ProductAllowedTypesProvider
{
    /**
     * Get product allowed types for given user.
     *
     * @param   User $user                  Commodity.
     *
     * @return  string[]                    Product allowed types set.
     */
    public function get(User $user): array
    {
        $resul = [
            CommodityProduct::TYPE_SELL
        ];

        if (in_array(User::ROLE_WHOLESALE_BUYER, $user->getRoles())) {
            $resul[] = CommodityProduct::TYPE_BUY;
        }

        return $resul;
    }
}
