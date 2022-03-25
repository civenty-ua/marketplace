<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotificationTitleLengthConstraint extends Constraint
{
    public $message = 'notification_title';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return NotificationTitleLengthValidator::class;
    }
}