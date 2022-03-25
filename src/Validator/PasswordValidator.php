<?php

namespace App\Validator;

use Symfony\Component\Validator\{
    Exception\UnexpectedTypeException,
    Constraint,
    ConstraintValidator,
};

class PasswordValidator extends ConstraintValidator
{
    private const LENGTH_MIN    = 8;
    private const LENGTH_MAX    = 255;

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PasswordConstraint) {
            throw new UnexpectedTypeException($constraint, PasswordConstraint::class);
        }

        if (!is_string($value) && !$value->getPlainPassword()) {
            return;
        }

        !is_string($value) ? $password = $value->getPlainPassword() : $password = $value;

        $hasUppercase   = preg_match('/\p{Lu}+/u', $password);
        $hasLowercase   = preg_match('/\p{Ll}+/u', $password);
        $hasNumber      = preg_match('@[0-9]@', $password);
        $completeValid  = preg_match('/([()*_\-!#$@%^&,.+"\'\][])*(?=.*[a-z])(?=.*[A-Z])/', $password);
        if (
            !$completeValid ||
            !$hasUppercase ||
            !$hasLowercase ||
            !$hasNumber ||
            strlen($password) < self::LENGTH_MIN ||
            strlen($password) > self::LENGTH_MAX
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('plainPassword')
                ->addViolation();
        }
    }
}