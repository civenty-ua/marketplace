<?php
declare(strict_types=1);

namespace App\Service\Market\CommodityActivity;

use Throwable;
use UnexpectedValueException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Market\{
    CommodityDatesCalculator,
    UserPublicationsCountProvider,
};
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityKit,
};
use App\Entity\Market\Notification\KitAgreementNotification;
/**
 * Market commodity activity manager.
 *
 * @package App\Service\Market
 */
class CommodityActivityManager
{
    private UserPublicationsCountProvider   $userPublicationsManager;
    private EntityManagerInterface          $entityManager;
    private CommodityDatesCalculator        $commodityDatesCalculator;
    /**
     * @param UserPublicationsCountProvider $userPublicationsManager
     * @param EntityManagerInterface        $entityManager
     * @param CommodityDatesCalculator      $commodityDatesCalculator
     */
    public function __construct(
        UserPublicationsCountProvider   $userPublicationsManager,
        EntityManagerInterface          $entityManager,
        CommodityDatesCalculator        $commodityDatesCalculator
    ) {
        $this->userPublicationsManager  = $userPublicationsManager;
        $this->entityManager            = $entityManager;
        $this->commodityDatesCalculator = $commodityDatesCalculator;
    }
    /**
     * Check, if commodity is fully active anf published.
     *
     * @param   Commodity $commodity                Commodity.
     *
     * @return  void
     * @throws  KitNotApprovedException             Commodity is kit, and it is not approved yet.
     * @throws  KitHasUnpublishedPartsException     Commodity is kit, and it has unpublished commodities.
     * @throws  AuthorStateControlException         Commodity author valid state control case.
     * @throws  ActivityTurnedOffException          Commodity activity is turned off case.
     * @throws  ActivityDatesControlException       Commodity activity dates control case.
     */
    public function checkCommodityIsPublished(Commodity $commodity): void
    {
        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            if (!$commodity->getIsApproved()) {
                throw new KitNotApprovedException();
            }
            if ($this->checkKitHasNotPublishedCommodities($commodity)) {
                throw new KitHasUnpublishedPartsException();
            }
        }

        if (!$this->checkCommodityAuthorStateIsSuitable($commodity)) {
            throw new AuthorStateControlException();
        }
        if (!$commodity->getIsActive()) {
            throw new ActivityTurnedOffException();
        }
        if (!$this->checkCommodityDatesActivityAreSuitable($commodity)) {
            throw new ActivityDatesControlException();
        }
    }
    /**
     * Check, if commodity can be activated by given user.
     *
     * @param   Commodity   $commodity              Commodity.
     * @param   User        $user                   Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case for such "commodity-user" combination.
     * @throws  KitHasUnpublishedPartsException     Commodity is kit, and it has unpublished commodities.
     * @throws  AuthorStateControlException         Commodity author valid state control case.
     * @throws  PublicationsCountControlException   Publications count luck of case.
     */
    public function checkCommodityCanBeActivated(Commodity $commodity, User $user): void
    {
        $userIsAdmin    = $this->checkUserIsAdmin($user);
        $userIsAuthor   = $user === $commodity->getUser();

        if (!$userIsAuthor && !$userIsAdmin) {
            throw new ActivityChangeAccessDeniedException();
        }

        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            if ($this->checkKitHasNotPublishedCommodities($commodity)) {
                throw new KitHasUnpublishedPartsException();
            }
        }

        if ($userIsAuthor && !$this->checkCommodityAuthorStateIsSuitable($commodity)) {
            throw new AuthorStateControlException();
        }
        if ($this->userPublicationsManager->getPublicationsCountLeft($commodity->getUser()) === 0) {
            throw new PublicationsCountControlException();
        }
    }
    /**
     * Check, if commodity can be deactivated by given user.
     *
     * @param   Commodity   $commodity              Commodity.
     * @param   User        $user                   Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case for such "commodity-user" combination.
     */
    public function checkCommodityCanBeDeactivated(Commodity $commodity, User $user): void
    {
        $userIsAdmin    = $this->checkUserIsAdmin($user);
        $userIsAuthor   = $user === $commodity->getUser();

        if (!$userIsAuthor && !$userIsAdmin) {
            throw new ActivityChangeAccessDeniedException();
        }
    }
    /**
     * Check, if commodity can be left by given user.
     *
     * @param   CommodityKit    $kit                Kit.
     * @param   User            $user               Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case.
     */
    public function checkKitCanBeLeft(CommodityKit $kit, User $user): void
    {
        if (
            $kit->getUser() === $user ||
            !$this->checkUserIsKitParticipant($kit, $user)
        ) {
            throw new ActivityChangeAccessDeniedException();
        }
    }
    /**
     * Check, if kit can be approved.
     *
     * @param   CommodityKit    $kit                Kit.
     * @param   User            $user               Action user.
     *
     * @return  void
     * @throws  KitApproveAccessDeniedException     Access denied case.
     * @throws  KitNotApprovedException             Commodity is kit, and it is not approved yet.
     */
    public function checkKitCanBeApproved(CommodityKit $kit, User $user): void
    {
        $userIsAdmin    = $this->checkUserIsAdmin($user);
        $userIsAuthor   = $user === $kit->getUser();

        if (
            !$userIsAdmin &&
            !$userIsAuthor &&
            !$this->checkUserIsKitParticipant($kit, $user)
        ) {
            throw new KitApproveAccessDeniedException();
        }

        if (!$this->checkKitHasAllApproves($kit)) {
            throw new KitNotApprovedException();
        }
    }
    /**
     * Try to activate commodity by user.
     *
     * @param   Commodity   $commodity              Commodity.
     * @param   User        $user                   Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case.
     */
    public function activateCommodity(Commodity $commodity, User $user): void
    {
        try {
            $this->checkCommodityCanBeActivated($commodity, $user);
        } catch (Throwable $exception) {
            throw new ActivityChangeAccessDeniedException();
        }

        $now                = new DateTime('now');
        $commodityIsNew     = !$commodity->getId();
        $commodityIsExpired = $commodity->getActiveTo() && $commodity->getActiveTo() < $now;

        try {
            $commodityActiveToDate = $this->commodityDatesCalculator->getCommodityActiveToDate($commodity);
        } catch (UnexpectedValueException $exception) {
            $commodityActiveToDate = null;
        }

        if ($commodityIsNew) {
            $commodity
                ->setIsActive(true)
                ->setActiveFrom($now)
                ->setActiveTo($commodityActiveToDate);
        } elseif ($commodityIsExpired) {
            $commodity
                ->setIsActive(true)
                ->setActiveTo($commodityActiveToDate);
        } else {
            $commodity
                ->setIsActive(true);
        }

        $this->entityManager->flush();
    }
    /**
     * Try to deactivate commodity by user.
     *
     * @param   Commodity   $commodity              Commodity.
     * @param   User        $user                   Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case.
     */
    public function deactivateCommodity(Commodity $commodity, User $user): void
    {
        try {
            $this->checkCommodityCanBeDeactivated($commodity, $user);
        } catch (Throwable $exception) {
            throw new ActivityChangeAccessDeniedException();
        }

        $commodity->setIsActive(false);
        $this->entityManager->flush();
    }
    /**
     * Try to left user from kit.
     *
     * @param   CommodityKit    $kit                Kit.
     * @param   User            $user               Action user.
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException Access denied case.
     */
    public function leftKit(CommodityKit $kit, User $user): void
    {
        try {
            $this->checkKitCanBeLeft($kit, $user);
        } catch (Throwable $exception) {
            throw new ActivityChangeAccessDeniedException();
        }

        $kit->setIsApproved(false);
        $this->entityManager->flush();
    }
    /**
     * Try to approve kit.
     *
     * @param   CommodityKit    $kit                Kit.
     * @param   User            $user               Action user.
     *
     * @return  void
     * @throws  KitApproveAccessDeniedException     Access denied case.
     * @throws  KitApprovedButDeactivatedException  Kit was approved but also was deactivated.
     */
    public function approveKit(CommodityKit $kit, User $user): void
    {
        try {
            $this->checkKitCanBeApproved($kit, $user);
        } catch (Throwable $exception) {
            throw new KitApproveAccessDeniedException();
        }

        $kit->setIsApproved(true);
        $this->entityManager->flush();

        if (
            $this->userPublicationsManager->getPublicationsCountLeft($kit->getUser()) === 0 ||
            !$kit->getIsActive()                                                            ||
            !$this->checkCommodityDatesActivityAreSuitable($kit)                            ||
            !$this->checkCommodityAuthorStateIsSuitable($kit)                               ||
            $this->checkKitHasNotPublishedCommodities($kit)
        ) {
            try {
                $this->deactivateCommodity($kit, $kit->getUser());
            } catch (ActivityChangeAccessDeniedException $exception) {
                $kit->setIsApproved(false);
                $this->entityManager->flush();
                throw new KitApproveAccessDeniedException($exception->getMessage(), 0, $exception);
            }

            throw new KitApprovedButDeactivatedException();
        }
    }
    /**
     * Check, if commodity activity dates are suitable.
     *
     * @param   Commodity $commodity                Commodity.
     *
     * @return  bool                                Activity dates are suitable.
     */
    private function checkCommodityDatesActivityAreSuitable(Commodity $commodity): bool
    {
        $now = new DateTime('now');

        return
            $commodity->getActiveFrom()  <  $now &&
            $commodity->getActiveTo()    >= $now;
    }
    /**
     * Check, if commodity author is in suitable state.
     *
     * @param   Commodity $commodity                Commodity.
     *
     * @return  bool                                Author state is suitable.
     */
    private function checkCommodityAuthorStateIsSuitable(Commodity $commodity): bool
    {
        $userHasNeedRole = false;

        switch ($commodity->getCommodityType()) {
            case Commodity::TYPE_PRODUCT:
                $userRequiredRoles = [
                    User::ROLE_SALESMAN,
                ];
                break;
            case Commodity::TYPE_SERVICE:
                $userRequiredRoles = [
                    User::ROLE_SERVICE_PROVIDER,
                ];
                break;
            case Commodity::TYPE_KIT:
                $userRequiredRoles = [
                    User::ROLE_SALESMAN,
                    User::ROLE_SERVICE_PROVIDER,
                ];
                break;
            default:
                return false;
        }

        if (!$commodity->getUser()) {
            return false;
        }

        foreach ($commodity->getUser()->getRoles() as $role) {
            if (in_array($role, $userRequiredRoles)) {
                $userHasNeedRole = true;
                break;
            }
        }

        return
            !$commodity->getUser()->getIsBanned()   &&
            $commodity->getUser()->isVerified()     &&
            $userHasNeedRole;
    }
    /**
     * Check, if kit includes not published commodities.
     *
     * @param   CommodityKit $kit                   Kit.
     *
     * @return  bool                                Kit has not published commodities.
     */
    private function checkKitHasNotPublishedCommodities(CommodityKit $kit): bool
    {
        foreach ($kit->getCommodities() as $kitCommodity) {
            if ($kitCommodity->getCommodityType() !== Commodity::TYPE_KIT) {
                try {
                    $this->checkCommodityIsPublished($kitCommodity);
                } catch (Throwable $exception) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Check, if user is kit participant.
     *
     * @param   CommodityKit    $kit                Kit.
     * @param   User            $user               Action user.
     *
     * @return  bool                                User is kit participant.
     */
    private function checkUserIsKitParticipant(CommodityKit $kit, User $user): bool
    {
        foreach ($kit->getCommodities() as $commodity) {
            if ($commodity->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
    /**
     * Check, if kit has all required approves.
     *
     * @param   CommodityKit $kit                   Kit.
     *
     * @return  bool                                Kit has all required approves.
     */
    private function checkKitHasAllApproves(CommodityKit $kit): bool
    {
        if (count($kit->getCoAuthors(false)) === 0) {
            return true;
        }

        /** @var KitAgreementNotification[] $notifications */
        $notifications          = $this
            ->entityManager
            ->getRepository(KitAgreementNotification::class)
            ->findBy([
                'status'    => KitAgreementNotification::STATUS_APPROVED,
                'commodity' => $kit,
            ]);
        $notificationsByUser    = [];

        foreach ($notifications as $notification) {
            $notificationsByUser[$notification->getReceiver()->getId()] = $notification;
        }

        foreach ($kit->getCoAuthors(false) as $coAuthor) {
            if (!isset($notificationsByUser[$coAuthor->getId()])) {
                return false;
            }
        }

        return true;
    }
    /**
     * Check if user is admin.
     *
     * @param   User $user                  User.
     *
     * @return  bool                        User is admin.
     */
    private function checkUserIsAdmin(User $user): bool
    {
        return
            in_array(User::ROLE_SUPER_ADMIN, $user->getRoles()) ||
            in_array(User::ROLE_ADMIN_MARKET, $user->getRoles());
    }
}
