<?php

namespace App\Validator;

use Symfony\Component\Validator\{
    Exception\UnexpectedTypeException,
    Constraint,
    ConstraintValidator,
};

class DeadUrlValidator extends ConstraintValidator
{


    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DeadUrlConstraint) {
            throw new UnexpectedTypeException($constraint, DeadUrlConstraint::class);
        }

        $regex = preg_match('(^/[^*]*$|^[^*]*(/\*)$)',$value->getDeadRequest());


        if ($regex == false || strlen($value->getDeadRequest()) <= 2) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('deadRequest')
                ->addViolation();
        }
    }
}