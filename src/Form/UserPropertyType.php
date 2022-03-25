<?php

namespace App\Form;

use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Market\Phone;
use App\Entity\Market\UserProperty;
use App\Entity\Region;
use App\Form\Field\FeedbackForm\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\{CollectionType, TextType, ChoiceType, UrlType};
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserPropertyType extends AbstractType
{

    // TODO: fields "required" parameter does not work. Find out why! Now "attr" parameter is used instead.
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Назва компанії',
            ])
            ->add('companyType', ChoiceType::class, [
                'label' => 'Тип компанії',
            ])
            ->add('address', TextType::class, [
                'label' => 'Адреса',
            ])
            ->add('facebookLink', UrlType::class, [
                'label' => 'Facebook',
            ])
            ->add('instagramLink', UrlType::class, [
                'label' => 'instagram',
            ])

            ->add('legalCompanyType', ChoiceType::class, [
                'label' => 'Тип власності компанії',
            ])
            ->add('commodityActiveToExtendedByDays',NumberType::class,[
                'label' => 'Продовження дати Активності Продуктів/Послуг/Пропозицій у днях',
            ])
            ->add('allowedAmountOfSellingCommodities',NumberType::class,[
                'label' => 'Дозволенна кількість Продуктів/Послуг/Пропозицій',
            ])

//            ->add('district', EntityType::class, [
//                'class' => District::class,
//                'placeholder' => '',
//                'label' => 'Район'
//            ])


//            ->add('locality', ChoiceType::class, [
//                'label' => 'Населений пункт',
//            ])
            ;

//        $formModifier = function (FormInterface $form, District $district = null) {
//            $localities = null === $district ? [] : $district->getLocalities();
//
//            $form->add('locality', EntityType::class, [
//                'class' => Locality::class,
//                'placeholder' => '',
//                'choices' => $localities,
//            ]);
//        };
//
//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($formModifier) {
//                /** @var UserProperty $data */
//                $data = $event->getData();
//
//                $formModifier($event->getForm(), $data->getDistrict());
//            }
//        );
//
//        $builder->get('district')->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event) use ($formModifier) {
//                // Тут важно вызвать $event->getForm()->getData(), так как
//                // $event->getData() предоставит вам пользовательские данные (т.е. ID)
//                $district = $event->getForm()->getData();
//
//                // так как мы добавили слушателя в дочернюю форму, нам нужно передать
//                // родительской форме функции обратнго вызова!
//                $formModifier($event->getForm()->getParent(), $district);
//            }
//        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserProperty::class,
        ]);
    }
}
