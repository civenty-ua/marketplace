<?php

namespace App\Form\Market;

use App\Entity\CompanyMessages;
use App\Entity\Crop;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Market\CompanyType;
use App\Entity\Market\LegalCompanyType;
use App\Entity\Market\UserCertificate;
use App\Entity\Market\UserProperty;
use App\Entity\Region;
use App\Entity\User;
use App\Form\Field\FeedbackForm\NumberType;
use App\Repository\DistrictRepository;
use App\Repository\LocalityRepository;
use App\Repository\Market\CompanyTypeRepository;
use App\Repository\RegionRepository;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\Extension\Core\Type\{CollectionType, TextType, TextareaType, EmailType, SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserPropertyCrudFormType extends UserPropertyFormType
{

    public function __construct(TokenStorageInterface $token)
    {
        parent::__construct($token);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('commodityActiveToExtendedByDays', NumberType::class, [
                'label' => 'Продовження дати Активності Продуктів/Послуг/Пропозицій у днях',
            ])
            ->add('allowedAmountOfSellingCommodities', NumberType::class, [
                'label' => 'Дозволенна кількість Продуктів/Послуг/Пропозицій',
            ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder, $options) {
                /** @var UserProperty|null $data */
                $data = $event->getData();
                $form = $event->getForm();

                if (!$data || !$data->getUser()) {
                    return;
                }

                $form->remove('companyType');

                $check = count(
                        array_intersect(
                            [
                                User::ROLE_SALESMAN,
                                User::ROLE_SERVICE_PROVIDER
                            ],
                            $data->getUser()->getRoles()
                        )
                    ) > 0;

                if ($check) {
                    $form->add('companyType', EntityType::class, [
                        'label' => 'role.companyType',
                        'choice_label' => 'name',
                        'multiple' => false,
                        'mapped' => true,
                        'required' => false,
                        'class' => CompanyType::class,
                        'query_builder' => function (CompanyTypeRepository $ctr) use ($data, $options) {
                            $roles = array_values(
                                array_flip(
                                    array_intersect(
                                        User::$rolesInRequestRoles,
                                        $data->getUser()->getRoles()
                                    )));
                            return $ctr->createQueryBuilder('ct')
                                ->where('ct.typeRole in (:typeRoles)')
                                ->setParameter(
                                    'typeRoles',
                                    $roles
                                );
                        },
                        'attr' => [
                            'class' => 'js-select2 select-input height-50',
                        ]
                    ]);
                }
            });
        $builder->remove('userCertificates');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => UserProperty::class,
            'query_builder' => null,
            'data_class' => UserProperty::class,
            'cascade_validation' => true,
            'allow_add' => true,
            'entityManager' => null,
            'role' => '',
        ]);
    }
}