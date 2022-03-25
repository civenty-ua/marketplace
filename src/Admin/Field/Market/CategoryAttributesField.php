<?php

namespace App\Admin\Field\Market;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use App\Form\Admin\Market\CategoryAttributesType;

class CategoryAttributesField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null, $fieldsConfig = []): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(CategoryAttributesType::class)
            ->setFormTypeOptions($fieldsConfig);
    }
}