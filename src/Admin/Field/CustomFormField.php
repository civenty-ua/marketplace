<?php

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class CustomFormField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName = '', ?string $label = null, $fieldsConfig = []): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setLabel(false)
            ->setFormType($fieldsConfig['form'])
            ->onlyOnForms()
            ;
    }
}