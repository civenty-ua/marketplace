<?php


namespace App\Validator;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class OldUserCountValidator extends ConstraintValidator
{
    public function validate($item, Constraint $constraint)
    {
        if (!$constraint instanceof OldUserCountConstraint) {
            throw new UnexpectedTypeException($constraint, OldUserCountConstraint::class);
        }
        if($item->getOldUserCount() && $item->getOldUserCount() > $item->getViewsAmount()){
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('oldUserCount')
                ->addViolation();
        }
    }
}