<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OldUserCountConstraint extends Constraint
{
    public $message = 'old_user_count';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return OldUserCountValidator::class;
    }
}