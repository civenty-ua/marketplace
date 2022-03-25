<?php

namespace App\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmptyType extends AbstractType
{

    public function getName(): string
    {
        return 'empty';
    }
}
