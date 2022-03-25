<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\Phone */

        if (null === $value || '' === $value) {
            return;
        }
        if(!preg_match('/^(\+38 \([0-9]{3}\) [0-9]{3} [0-9]{2} [0-9]{2})$/i', $value, $output_array))
        {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
