<?php
declare(strict_types=1);

namespace App\Service\Market;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{
    User,
    Market\Commodity,
    Market\UserProperty,
};

use function is_null;
/**
 * Market user publications manager.
 *
 * @package App\Service\Market
 */
class UserPublicationsCountProvider
{
    private EntityManagerInterface $entityManager;
    private array $allowedPublicationsCount = [];
    private array $currentPublicationsCount = [];
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * Get allowed publications count for given user.
     *
     * @param   User $user                  User.
     *
     * @return  int                         Publications count allowed.
     */
    public function getAllowedPublicationsCount(User $user): int
    {
        if (!isset($this->allowedPublicationsCount[$user->getId()])) {
            $userPublicationsCountParameter = $user->getUserProperty()
                ? $user->getUserProperty()->getAllowedAmountOfSellingCommodities()
                : null;

            $this->allowedPublicationsCount[$user->getId()] =
                !is_null($userPublicationsCountParameter) &&
                $userPublicationsCountParameter >= 0
                    ? $userPublicationsCountParameter
                    : UserProperty::ALLOWED_COMMODITIES_DEFAULT;
        }

        return $this->allowedPublicationsCount[$user->getId()];
    }
    /**
     * Get current publications count for given user.
     *
     * @param   User $user                  User.
     *
     * @return  int                         Publications count current.
     */
    public function getCurrentPublicationsCount(User $user): int
    {
        if (!isset($this->currentPublicationsCount[$user->getId()])) {
            $this->currentPublicationsCount[$user->getId()] = $this
                ->entityManager
                ->getRepository(Commodity::class)
                ->getTotalCount(null, [
                    'user'      => $user->getId(),
                    'activity'  => true,
                ]);
        }

        return $this->currentPublicationsCount[$user->getId()];
    }
    /**
     * Get publications count left for given user.
     *
     * @param   User $user                  User.
     *
     * @return  int                         Publications count left.
     */
    public function getPublicationsCountLeft(User $user): int
    {
        $allowedPublicationsCount   = $this->getAllowedPublicationsCount($user);
        $currentPublicationsCount   = $this->getCurrentPublicationsCount($user);

        return $allowedPublicationsCount > $currentPublicationsCount
            ? $allowedPublicationsCount - $currentPublicationsCount
            : 0;
    }
}
