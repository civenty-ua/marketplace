<?php

namespace App\Form\Profile;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\{
    FormBuilderInterface,
    AbstractType,
};
use Symfony\Component\Form\Extension\Core\Type\{
    BirthdayType,
    CheckboxType,
    ChoiceType,
    TelType,
    TextType,
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use App\Entity\{
    Activity,
    Crop,
    Region,
};
/**
 * Profile base fields form.
 */
class ProfileEditFormType extends AbstractType
{
    protected ?string $currentLocale;

    public function __construct(RequestStack $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('avatar', VichFileType::class, [
                'required' => false,
                'mapped' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'attr' => [
                    'readonly' => 'readonly',
                    'accept' => 'image/*',
                    'title' => 'Вибрати інше зображення'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'form_registration.name',
                'attr' => [
                    'class' => 'form-text',
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'form_registration.phone',
                'attr' => [
                    'class' => 'js-phone-mask form-text js-user-phone-input',
                    'placeholder' => '+38 XXX XXX XX XX',
                ]
            ])
            ->add('activity', EntityType::class, [
                'label' => 'profile.forms.profile_edit.activity',
                'choice_label' => 'name',
                'class' => Activity::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('activity')
                        ->leftJoin('activity.translations', 'activityTranslations')
                        ->where('activityTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('activityTranslations.name', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ],
            ])
            ->add('region', EntityType::class, [
                'label' => 'form_registration.region',
                'choice_label' => 'name',
                'class' => Region::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('region')
                        ->leftJoin('region.translations', 'regionTranslations')
                        ->where('regionTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->addOrderBy('region.sort', 'ASC')
                        ->addOrderBy('regionTranslations.name', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ],
            ])
            ->add('crops', EntityType::class, [
                'label' => 'profile.forms.profile_edit.user_crops',
                'choice_label' => 'name',
                'multiple' => true,
                'mapped' => true,
                'required' => false,
                'class' => Crop::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('crops')
                        ->leftJoin('crops.translations', 'cropsTranslations')
                        ->where('cropsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('cropsTranslations.name', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'form_registration.gender_label',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'form_registration.gender.men' => 0,
                    'form_registration.gender.women' => 1,
                ],
                'choice_attr' => function () {
                    return ['class' => 'with-gap'];
                },
                'attr' => [
                    'class' => 'form__gender-block',
                ],
            ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'form_registration.date_of_birth',
                'choice_translation_domain' => true,
                'widget' => 'choice',
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
            ->add('isNewsSub', CheckboxType::class, [
                'label' => 'form_registration.agree_subscribe',
                'required' => false
            ])
            ->add('code', TextType::class, [
                'label' => 'form_registration.enter_code',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-text code-input',
                    'placeholder' => 'XX XX XX',
                    'id' => 'verification-input'
                ]
            ])
        ;
    }
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'mapped' => true
        ]);
    }
}
