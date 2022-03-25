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
use App\Repository\DistrictRepository;
use App\Repository\LocalityRepository;
use App\Repository\Market\CompanyTypeRepository;
use App\Repository\RegionRepository;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\Extension\Core\Type\{CollectionType, TextType, TextareaType, EmailType, SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserPropertyFormType extends AbstractType
{
    protected $user;

    public function __construct(TokenStorageInterface $token)
    {
        $this->user = $token->getToken()->getUser();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('companyName',
                TextType::class,
                [
                    'block_prefix' => 'profile_market_text_field',
                    'label' => 'Назва',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'role.title',
                    ],
                    'required' => false
                ])
            ->add('address',
                TextType::class,
                [
                    'block_prefix' => 'profile_market_text_field',
                    'label' => 'Адреса',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'role.address',
                    ],
                    'required' => false
                ])
            ->add('facebookLink',
                TextType::class,
                [
                    'block_prefix' => 'profile_market_text_field',
                    'label' => 'Facebook URL',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'role.facebookLink',
                    ],
                ])
            ->add('instagramLink',
                TextType::class,
                [
                    'block_prefix' => 'profile_market_text_field',
                    'label' => 'Instagram URL',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'role.instagramLink',
                    ],
                ])
            ->add('legalCompanyType', EntityType::class, [
                'block_prefix' => 'profile_market_select_field',
                'label' => 'role.legalCompanyType',
                'choice_label' => 'name',
                'multiple' => false,
                'mapped' => true,
                'required' => false,
                'class' => LegalCompanyType::class,
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ]
            ])
            ->add('companyType', EntityType::class, [
                'block_prefix' => 'profile_market_select_field',
                'label' => 'role.companyType',
                'choice_label' => 'name',
                'multiple' => false,
                'mapped' => true,
                'required' => false,
                'class' => CompanyType::class,
                'query_builder' => function (CompanyTypeRepository $ctr) use ($options) {
                    return $ctr->createQueryBuilder('ct')
                        ->andWhere('ct.typeRole = :typeRole')
                        ->addOrderBy('ct.name', 'ASC')
                        ->setParameters(['typeRole' => $options['role']]);
                },
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ]
            ]);

        $this->buildRulesAccordingToUserRoleRequest($builder, $options['role']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProperty::class,
            'cascade_validation' => true,
            'allow_add' => true,
            'entityManager' => null,
            'role' => '',
        ]);
    }

    private function buildRulesAccordingToUserRoleRequest(FormBuilderInterface $builder,string $role)
    {
        switch ($role){
            case 'wholesale-bayer':
                $builder->get('legalCompanyType')->setRequired(true);
                break;
            case 'service-provider':
                $builder->get('legalCompanyType')->setRequired(true);
                break;
           /* case User::ROLE_SALESMAN:
                $builder->remove('companyType');
                break;*/
        }
        $builder->remove('companyType');
    }
}