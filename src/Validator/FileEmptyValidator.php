<?php

namespace App\Validator;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FileEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FileEmpty) {
            throw new UnexpectedTypeException($constraint, FileEmpty::class);
        }
        $name = $value->getFileName();
        $file = $value->getFile();
        if (empty($file) and (is_null($name) or empty($name))) {
            $this->context->buildViolation($constraint->message)
                ->atPath('file')
                ->addViolation();
        }
    }
}