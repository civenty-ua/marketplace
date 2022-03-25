<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateOfBirthValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DateOfBirth) {
            throw new UnexpectedTypeException($constraint, DateOfBirth::class);
        }
        $value instanceof \DateTime
            ? $dateOfBirth = $value
            : $dateOfBirth = $value->getDateOfBirth();

        if ($dateOfBirth >= new \DateTime('now')) {
            $this->context->buildViolation($constraint->message)
                ->atPath('dateOfBirth')
                ->addViolation();
        }

    }
}