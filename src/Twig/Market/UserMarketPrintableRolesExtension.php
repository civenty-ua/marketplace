<?php
declare(strict_types = 1);

namespace App\Twig\Market;

use Twig\{
    TwigFilter,
    Extension\AbstractExtension,
};
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\User;
/**
 * Market, user printable roles maker.
 *
 * @package App\Twig
 */
class UserMarketPrintableRolesExtension extends AbstractExtension
{
    private const MARKET_ROLES = [
        User::ROLE_SALESMAN,
        User::ROLE_SERVICE_PROVIDER,
        User::ROLE_WHOLESALE_BUYER,
    ];

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('marketUserPrintableRoles', [$this, 'getPrintableRoles']),
        ]);
    }
    /**
     * Get user (for market only) roles in printable condition.
     *
     * @param   User $user                  User.
     *
     * @return  string                      Roles in printable condition.
     */
    public function getPrintableRoles(User $user): string
    {
        $result = [];

        foreach ($user->getRoles() as $role) {
            if (in_array($role, self::MARKET_ROLES)) {
                $result[] = $this->translator->trans("user.roles.$role");
            }
        }

        return implode(', ', $result);
    }
}
