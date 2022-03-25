<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class YouTubeUrlValidator extends ConstraintValidator
{
    public function validate($videoItem, Constraint $constraint)
    {
        if (!$constraint instanceof YouTubeUrl) {
            throw new UnexpectedTypeException($constraint, YouTubeUrl::class);
        }

        if ($videoItem->getVideoId() == '') {
            $this->context->buildViolation($constraint->message)
                ->atPath('videoId')
                ->addViolation();
        }
    }
}