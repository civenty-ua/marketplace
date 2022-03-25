<?php

namespace App\Form;

use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\{
    BirthdayType,
    CheckboxType,
    ChoiceType,
    EmailType,
    PasswordType,
    TelType,
    TextType,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use App\Form\Field\CheckboxWithLinkType;
use App\Entity\{
    Options,
    Region,
    User,
};
/**
 * Registration form.
 */
class RegistrationFormType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager Entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Options[] $options */
        $options                = $this->entityManager
            ->getRepository(Options::class)
            ->findBy([
                'code' => [
                    'rules_agreement_link',
                    'personal_data_process_agreement_link',
                ],
            ]);
        $optionsValues          = [];
        $passwordPlaceholder    = '***********';

        foreach ($options as $option) {
            $optionsValues[$option->getCode()] = $option->getValue();
        }

        $builder
            ->add('email',
                EmailType::class,
                [
                    'label' => 'form_registration.email',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'petrenko@gmail.com',
                    ],
                    'translation_domain' => 'messages',
                ])
            ->add('name',
                TextType::class,
                [
                    'label' => 'form_registration.name',
                    'attr' => [
                        'class' => 'form-text',
                    ],
                    'translation_domain' => 'messages',
                ])
            ->add('phone',
                TelType::class,
                [
                    'label' => 'form_registration.phone',
                    'attr' => [
                        'class' => 'js-phone-mask form-text js-user-phone-input',
                        'placeholder' => '+38 (___) ___ __ __'
                    ],
                    'required' => true,
                    'translation_domain' => 'messages',
                ])
            ->add('region',
                EntityType::class,
                [
                    'class' => Region::class,
                    'placeholder'   => 'form_registration.region_select',
                    'label' => 'form_registration.region',
                    'attr' => [
                        'class' => 'select-input height-50',
                    ],
                    'translation_domain' => 'messages',
                    'query_builder' => function (RegionRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->leftJoin('r.translations', 'rt')
                            ->where("rt.locale = 'uk'")
                            ->addOrderBy('r.sort', 'ASC')
                            ->addOrderBy('rt.name', 'ASC');
                    }
                ])
            ->add('gender',
                ChoiceType::class,
                [
                    'label' => 'form_registration.gender_label',
                    'attr' => [
                        'class' => 'form__gender-block',
                    ],
                    'translation_domain' => 'messages',
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        'form_registration.gender.men' => 0,
                        'form_registration.gender.women' => 1,
                    ],
                    'choice_attr' => function () {
                        return ['class' => 'with-gap'];
                    },
                ])
            ->add('dateOfBirth',
                BirthdayType::class,
                [
                    'label' => 'form_registration.date_of_birth',
                    'translation_domain' => 'messages',
                    'choice_translation_domain' => true,
                    'widget' => 'choice',
                    'data' => new \DateTime('1990-01-01'),
                    'legacy_error_messages' => true,
                    'months' => [
                        'Jan' => 1,
                        'Feb' => 2,
                        'Mar' => 3,
                        'Apr' => 4,
                        'May' => 5,
                        'Jun' => 6,
                        'Jul' => 7,
                        'Aug' => 8,
                        'Sep' => 9,
                        'Oct' => 10,
                        'Nov' => 11,
                        'Dec' => 12,
                    ],
                ])
            ->add('agreeTerms',
                CheckboxWithLinkType::class,
                [
                    'label' => 'form_registration.agree_terms',
                    'translation_domain' => 'messages',
                    'mapped' => false,
                    'constraints' => [
                        new IsTrue([
                            'message' => 'You should agree to our terms.',
                        ]),
                    ],
                    'link' => $optionsValues['rules_agreement_link'] ?? '',
                ])
            ->add('agreeTerms2',
                CheckboxWithLinkType::class,
                [
                    'label' => 'form_registration.agree_terms2',
                    'translation_domain' => 'messages',
                    'mapped' => false,
                    'constraints' => [
                        new IsTrue([
                            'message' => 'You should agree to our terms.',
                        ]),
                    ],
                    'link' => $optionsValues['personal_data_process_agreement_link'] ?? '',
                ])
            ->add('isNewsSub',
                CheckboxType::class,
                [
                    'label' => 'form_registration.agree_subscribe',
                    'translation_domain' => 'messages',
                    'required' => false,
                ])
            ->add('code',
                TextType::class,
                [
                    'label' => 'form_registration.enter_code',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-text code-input',
                        'placeholder' => 'XX XX XX',
                        'id' => 'verification-input'
                    ],
                    'translation_domain' => 'messages',
                ])
            ->add('plainPassword',
                PasswordType::class,
                [
                    'label' => 'form_registration.plain_password',
                    'attr' => [
                        'class' => 'js-password-input form-text  new-password',
                        'placeholder' => $passwordPlaceholder,
                    ],
                    'help' => 'form_registration.password_regex',
                    'help_attr' => [ 'class' => 'personal-area-form-help-wrapper'],
                    'translation_domain' => 'messages',
                ])
            ->add('passwordConfirm',
                PasswordType::class,
                [
                    'label' => 'form_registration.confirm_password',
                    'attr' => [
                        'class' => 'js-password-input form-text',
                        'placeholder' => $passwordPlaceholder,
                        'display' => 'none'
                    ],
                    'translation_domain' => 'messages',
                    'mapped' => false
                ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3([
                    'message' => 'form_registration.captcha',
                    'messageMissingValue' => 'form_registration.captcha_mis'
                ]),
                'action_name' => 'login'
            ]);
    }
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
