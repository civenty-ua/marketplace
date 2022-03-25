<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OnlySixReviews extends Constraint
{
    public $message = 'На головній може бути тільки 6 Відгуків, відключіть один перед тим як додати новий.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return ReviewValidator::class;
    }
}