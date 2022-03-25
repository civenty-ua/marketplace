<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateOfBirth extends Constraint
{
    public $message = 'Дата народження не може бути майбутнім';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return DateOfBirthValidator::class;
    }
}