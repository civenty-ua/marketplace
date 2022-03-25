<?php

namespace App\Form\Market;

use App\Entity\Market\Notification\PriceOfferNotification;
use Doctrine\Common\Collections\Expr\Value;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class OfferPriceFormType extends AbstractType
{
    private $user;

    public function __construct(TokenStorageInterface $token)
    {
        $this->user = $token->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $phones = $this->user->getPhones()->getValues();
        $result = [];
        if($this->user->getPhone() !== null)
        {
            $result = array_merge($result, [$this->user->getPhone() => $this->user->getPhone()]);
        }
        foreach ($phones as $phone){
            $result = array_merge($result,[$phone->getPhone() => $phone->getPhone()]);
        }
        $builder

            ->add('name', TextType::class,[
                'attr' => [
                    'readonly'=>'readonly'
                ],
                'label' => 'form_offer_price.fio'
            ])
            ->add('phone', ChoiceType::class,[
                'block_prefix' => 'profile_market_select_field',
                'choices' => $result,
                'label' => 'form_offer_price.phone'
            ])
            ->add('message', TextareaType::class, [
                'block_prefix' => 'profile_market_textarea_field',
                'constraints' => [
                    new NotBlank(null, null, true),
                    new Length(['min' => 3,'max' => 1000]),
                ],
                'label' => 'form_offer_price.message'
            ])
            ->add('price', NumberType::class,[
                'label' => 'form_offer_price.ofefer_price',
                'constraints' => [
                    new NotBlank(),
                    new PositiveOrZero(),
                ],
            ])
            ->add('save',SubmitType::class,[
                'label' => 'form_offer_price.confirm',
                'attr' =>['class' =>'btn-h50-green', 'hidden'=>'hidden']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceOfferNotification::class,
        ]);

    }
}
