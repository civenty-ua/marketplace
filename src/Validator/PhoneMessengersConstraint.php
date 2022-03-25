<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PhoneMessengersConstraint extends Constraint
{
    public $message = 'phone_messenger';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return PhoneMessengersValidator::class;
    }
}