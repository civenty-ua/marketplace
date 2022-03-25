<?php
declare(strict_types = 1);

namespace App\Validator;

use Doctrine\ORM\PersistentCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator,
};
use Symfony\Component\Security\Core\Security;
use App\Entity\{
    User,
    Market\Commodity,
};

class KitCommoditiesValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;
    private ?User               $currentUser;
    /**
     * Constructor.
     *
     * @param TranslatorInterface   $translator
     * @param Security              $security
     */
    public function __construct(TranslatorInterface $translator, Security $security)
    {
        $this->translator   = $translator;
        $this->currentUser  = $security->getUser();
    }
    public function validate($value, Constraint $constraint)
    {
        /**
         * @var PersistentCollection|Commodity[]    $value
         * @var KitCommoditiesConstraint            $constraint
         */

        if ($value->count() < $constraint->min) {
            $message = $this->translator->trans('market.profile.kitForm.validation.commoditiesCountNotEnough', [
                '#COUNT#' => $constraint->min,
            ]);
            $this->context->addViolation($message);
        }
        elseif ($value->count() > $constraint->max) {
            $message = $this->translator->trans('market.profile.kitForm.validation.commoditiesCountToMuch', [
                '#COUNT#' => $constraint->max,
            ]);
            $this->context->addViolation($message);
        }

        if ($constraint->requiredCreatorCommodity) {
            $hasCreatorCommodities = false;
            foreach ($value as $commodity) {
                if ($commodity->getUser() === $this->currentUser) {
                    $hasCreatorCommodities = true;
                    break;
                }
            }

            if (!$hasCreatorCommodities) {
                $message = $this->translator->trans('market.profile.kitForm.validation.creatorCommodityAbsent');
                $this->context->addViolation($message);
            }
        }
    }
}