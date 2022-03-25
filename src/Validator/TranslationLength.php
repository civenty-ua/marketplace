<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TranslationLength extends Constraint
{
    public $message = 'Поля метатегів description та keywords повинні мати менше ніж ~240 символів.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    public function  validatedBy()
    {
        return TranslationValidator::class;
    }
}