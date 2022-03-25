<?php

namespace App\Validator;

use App\Entity\Market\Phone;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneMessengersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $array = [];

        if (!$constraint instanceof PhoneMessengersConstraint) {
            throw new UnexpectedTypeException($constraint, PhoneMessengersConstraint::class);
        }
        if ($value instanceof PersistentCollection) {
            foreach ($value as $phone) {
                $array[] = $phone->getPhone();
            }
            $this->checkPhone($array, $constraint);
        }

    }

    private function checkPhone(array $phone, PhoneMessengersConstraint $constraint)
    {
        if (count($phone) !== count(array_unique($phone))) {
            $this->callBuildViolation($constraint);
        }
    }

    private function callBuildViolation($constraint)
    {
        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}