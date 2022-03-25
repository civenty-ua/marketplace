<?php

namespace App\Form\Market;

use App\Entity\Market\Phone;
use App\Entity\Market\UserCertificate;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType,
    HiddenType,
    TextType,
    TextareaType,
    EmailType,
    SubmitType,
    FileType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserCertificateFormType extends AbstractType
{

    private $user;

    public function __construct(TokenStorageInterface $token)
    {
        $this->user = $token->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('file', VichFileType::class, [
                'block_prefix'      => 'profile_market_image_field',
                'required'          => true,
                'error_bubbling'    => false,
                'attr' => [
                    'maxSize' => 5*1024*1024,
                    'allowMimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/pjpeg',
                        'application/pdf',
                        'application/x-pdf'
                    ]
                ]
            ])
            ->add('name',
                TextType::class,
                [
                    'label' => 'role.name_certificate',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'role.name_certificate',
                    ],
                ])
            ->add('submit',
                SubmitType::class,
                [
                    'label' => 'role.add_certificate',
                    'attr' => [
                        'class' => 'square-button',
                        'placeholder' => 'role.name_certificate'
                    ],
                ]);
        $this->setEcologyCheckbox($builder);
    }

    private function setEcologyCheckbox(FormBuilderInterface $builder): void
    {
        $readonly = [];
        if (!in_array(User::ROLE_SALESMAN, $this->user->getRoles())) {
            $readonly = [
                'attr' =>
                [
                    'disabled' => 'disabled',
                    'data-bs-toggle' => "tooltip",
                    'title' => "Функціональність недоступна"
                ]
            ];
        }
        $builder->add('isEcology', CheckboxType::class, array_merge([
            'label' => 'role.is_ecology',
            'row_attr' => [
                'class' => 'checkbox-wrap'
            ],
            'required' => false
        ], $readonly));

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCertificate::class,
            'cascade_validation' => true,
        ]);
    }
}
