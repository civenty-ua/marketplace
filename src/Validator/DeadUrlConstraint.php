<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DeadUrlConstraint extends Constraint
{
    public $message = 'dead_url_asterisk';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return DeadUrlValidator::class;
    }
}