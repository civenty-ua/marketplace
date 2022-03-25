<?php

namespace App\Validator;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintValidator;

class TranslationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        if (!$constraint instanceof TranslationLength) {
            throw new UnexpectedTypeException($constraint, TranslationLength::class);
        }

        foreach ($value->getTranslations()->toArray() as $trans) {
            if ($trans->getKeywords() && mb_strlen($trans->getKeywords()) > 255) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('keywords')
                    ->addViolation();
            }
            if ($trans->getDescription() && mb_strlen($trans->getDescription()) > 255) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('description')
                    ->addViolation();
            }
        }

    }
}