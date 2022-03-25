<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use DateTime;
use Doctrine\ORM\{
    EntityRepository,
    EntityManagerInterface,
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\{
    AbstractType,
    FormEvent,
    FormEvents,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\{
    NumberType,
    SubmitType,
    TextType,
    TextareaType,
};
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\Market\ProductAllowedTypesProvider;
use App\Entity\{
    User,
    Market\Commodity,
    Market\Phone,
};
/**
 * Profile, commodity abstract form.
 */
abstract class CommodityFormType extends AbstractType
{
    protected EntityManagerInterface        $entityManager;
    protected TranslatorInterface           $translator;
    protected ProductAllowedTypesProvider   $productAllowedTypesProvider;
    protected ?User                         $currentUser;
    protected ?string                       $currentLocale;
    /**
     * Constructor.
     *
     * @param EntityManagerInterface        $entityManager
     * @param TranslatorInterface           $translator
     * @param ProductAllowedTypesProvider   $productAllowedTypesProvider
     * @param Security                      $security
     * @param RequestStack                  $requestStack
     */
    public function __construct(
        EntityManagerInterface      $entityManager,
        TranslatorInterface         $translator,
        ProductAllowedTypesProvider $productAllowedTypesProvider,
        Security                    $security,
        RequestStack                $requestStack
    ) {
        $this->entityManager                = $entityManager;
        $this->translator                   = $translator;
        $this->productAllowedTypesProvider  = $productAllowedTypesProvider;
        $this->currentUser                  = $security->getUser();
        $this->currentLocale                = $requestStack->getCurrentRequest()->getLocale();
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('title', TextType::class, [
                'block_prefix'      => 'profile_market_text_field',
                'required'          => true,
                'error_bubbling'    => true,
            ])
            ->add('description', TextareaType::class, [
                'block_prefix'      => 'profile_market_textarea_field',
                'required'          => true,
                'error_bubbling'    => true,
                'attr'              => [
                    'maxlength' => 1000,
                ],
            ])
            ->add('price', NumberType::class, [
                'block_prefix'      => 'profile_market_number_field',
                'required'          => true,
                'error_bubbling'    => true,
            ])
            ->add('userDisplayPhones', EntityType::class, [
                'block_prefix'      => 'profile_market_select_field',
                'class'             => Phone::class,
                'required'          => false,
                'error_bubbling'    => true,
                'multiple'          => true,
                'choice_label'      => 'phone',
                'query_builder'     => function (EntityRepository $entityRepository) {
                    $alias = 'userDisplayPhones';

                    return $entityRepository
                        ->createQueryBuilder($alias)
                        ->where("$alias.user = :user")
                        ->setParameters([
                            'user' => $this->currentUser,
                        ]);
                },
            ])
            ->add('submit', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var Commodity|null $commodity */
                $commodity = $event->getData();

                if (!$commodity->getId()) {
                    $commodity
                        ->setActiveFrom(new DateTime('now'))
                        ->setActiveTo(new DateTime('now'))
                        ->setUser($this->currentUser);
                }
            });
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
}
