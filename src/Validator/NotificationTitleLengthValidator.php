<?php

namespace App\Validator;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintValidator;

class NotificationTitleLengthValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        if (!$constraint instanceof NotificationTitleLengthConstraint) {
            throw new UnexpectedTypeException($constraint, NotificationTitleLengthConstraint::class);
        }
        if ($value->getTitle() && mb_strlen($value->getTitle()) > 255) {
            $this->context->buildViolation($constraint->message)
                ->atPath('title')
                ->addViolation();
        }


    }
}