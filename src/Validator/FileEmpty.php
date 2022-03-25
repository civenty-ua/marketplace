<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FileEmpty extends Constraint
{
    public $message = 'Повинен бути вибраний файл сертифікату';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return static::class.'Validator';
    }
}