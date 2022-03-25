<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YouTubeLink extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'market.profile.aboutMe.descriptionVideoLink.error';
    public $notExist = 'market.profile.aboutMe.descriptionVideoLink.notExist';
}
