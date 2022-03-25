<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsZoomMeeting extends Constraint
{
    public $message = 'The zoomMeeting with this id not exists,please check it or partner ZoomApiKey';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return ZoomValidator::class;
    }
}