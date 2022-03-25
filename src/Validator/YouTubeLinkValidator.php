<?php

namespace App\Validator;

use App\Service\YoutubeClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class YouTubeLinkValidator extends ConstraintValidator
{
    private $youTubeService;
    private $translator;
    public function __construct(YoutubeClient $youTubeService, TranslatorInterface $translator)
    {
        $this->youTubeService = $youTubeService;
        $this->translator = $translator;
    }
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\YouTubeLink */

        if (null === $value || '' === $value) {
            return;
        }

        try {
            $itemId = Request::create($value)->query->get('v');
            if(!$itemId){
                $this->context->buildViolation($this->translator->trans($constraint->message))
                    ->addViolation();
                return;
            }
            if(!$itemId || count($this->youTubeService->read($itemId)['items']) == 0){
                $this->context->buildViolation($this->translator->trans($constraint->notExist))
                    ->addViolation();
            }
        }
        catch (\Exception $e){

        }
    }
}
