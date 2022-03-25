<?php
declare(strict_types = 1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class KitCommoditiesConstraint extends Constraint
{
    public int  $min                        = 10;
    public int  $max                        = 20;
    public bool $requiredCreatorCommodity   = false;

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return KitCommoditiesValidator::class;
    }
}