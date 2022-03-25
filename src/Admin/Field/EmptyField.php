<?php

namespace App\Admin\Field;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Form\Field\EmptyType;
use Doctrine\DBAL\Types\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

final class EmptyField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName = '', ?string $label = null, $fieldsConfig = []): self
    {
        return (new self())
            ->setProperty('emptyField')
            ->setValue('')
            ->setVirtual(true)
            ->setLabel(' ')
            ->setCssClass('clearfix')
            ->setVirtual(true)
            ->setFormType(EmptyType::class)
            ->onlyOnForms()
            ;
    }
}