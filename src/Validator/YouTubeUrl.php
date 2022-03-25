<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YouTubeUrl extends Constraint
{
    public $message = 'This value is not a valid URL.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return YouTubeUrlValidator::class;
    }
}