<?php

namespace App\Controller\Admin;

use App\Admin\Field\CustomFormField;
use App\Admin\Filter\UserRoleFilter;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Market\RequestRole;
use App\Entity\Market\UserProperty;
use App\Entity\Region;
use App\Entity\User;
use App\Form\Market\UserPropertyCrudFormType;
use App\Form\Market\UserPropertyFormType;
use App\Security\EmailVerifier;
use App\Form\UserPhoneType;
use App\Form\UserPropertyType;
use App\Service\ExportService;
use App\Service\FileManager\FileManagerInterface;
use App\Service\FileManager\Mapping\UserAvatarMapping;
use App\Traits\LocationTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserCrudController extends BaseCrudController
{
    use ResetPasswordControllerTrait;
    use LocationTrait;

    private FileManagerInterface $fileManager;
    private UserPasswordEncoderInterface $encoder;
    protected TranslatorInterface $translator;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private MailerInterface $mailer;
    private AdminUrlGenerator $urlGenerator;
    private RequestStack $requestStack;
    private EmailVerifier $emailVerifier;
    protected ?string $currentLocale;


    public function __construct(
        FileManagerInterface         $fileManager,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface          $translator,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface              $mailer,
        AdminUrlGenerator            $urlGenerator,
        RequestStack                 $requestStack,
        EmailVerifier                $emailVerifier
    )
    {
        $this->fileManager = $fileManager;
        $this->encoder = $encoder;
        $this->translator = $translator;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->emailVerifier = $emailVerifier;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function setEncoder(UserPasswordEncoderInterface $encoder): void
    {
        $this->encoder = $encoder;
    }


    public function configureFields(string $pageName): iterable
    {

        $avatarPath = $this->fileManager->getUploadPath(UserAvatarMapping::class);
        yield FormField::addPanel('Головне');
        yield IdField::new('id')->hideOnForm();
        yield DateField::new('createdAt', 'Дата реєстрації')->onlyOnIndex();
        yield BooleanField::new('isBanned')
            ->setColumns('col-sm-6 ')
            ->setLabel('Заблокований')
            ->onlyOnForms();
        yield TextField::new('status')
            ->setColumns('col-sm-6 ')
            ->setLabel('Cтатус')
            ->formatValue(function ($value, User $user) {
                return $user->getIsBanned() == true ? sprintf('Заблоковано') : sprintf('Активний');

            })->setVirtual(true)->onlyOnIndex();
        yield BooleanField::new('isVerified')
            ->setLabel('admin.dashboard.user.email_confirm')->onlyOnForms();
        yield BooleanField::new('isNewsSub')
            ->setLabel('admin.dashboard.user.news_sub')->onlyOnForms();
        yield DateField::new('createdAt', 'Дата реєстрації')
            ->onlyOnForms()->setFormTypeOptions(['disabled' => true]);
        yield TextField::new('name')
            ->setColumns('col-sm-6')
            ->setLabel('form_registration.name')
            ->formatValue(function ($value, User $user) {
                $link = $this->urlGenerator
                    ->setController(UserCrudController::class)
                    ->setAction(Action::EDIT)
                    ->set('entityId', $user->getId())
                    ->generateUrl();
                $linkTitle = $user->getName();

                return "<a href=\"$link\">$linkTitle</a>";
            });
        yield AssociationField::new('activity')
            ->setLabel('menu.about_us.activity')
            ->setColumns('col-sm-6')
            ->setFormTypeOptions([
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('activity')
                        ->leftJoin('activity.translations', 'activityTranslations')
                        ->where('activityTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('activityTranslations.name', 'ASC');
                },
            ])
            ->onlyOnForms();
        yield AssociationField::new('crops')
            ->setLabel('item.crops')
            ->setColumns('col-sm-6')
            ->setFormTypeOptions([
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('crops')
                        ->leftJoin('crops.translations', 'cropsTranslations')
                        ->where('cropsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('cropsTranslations.name', 'ASC');
                },
            ])
            ->onlyOnForms();
        yield TextField::new('email')
            ->setColumns('col-sm-6')
            ->setLabel('form_registration.email');

        if ($pageName === Crud::PAGE_NEW) {
            yield TextField::new('plainPassword')
                ->setLabel('form_registration.plain_password')
                ->setRequired(true)
                ->onlyOnForms();
        }
        yield TelephoneField::new('phone')
            ->setColumns('col-sm-6 ')
            ->setFormTypeOptions([
                'attr' => [
                    'class' => 'js-phone-mask form-text js-user-phone-input',
                    'placeholder' => '+38 (___) ___ __ __'
                ],
            ])
            ->setLabel('form_registration.phone');

        $rolesChoices = [];
        foreach (User::getAvailableRoles() as $role) {
            $rolesChoices["user.roles.$role"] = $role;
        }

        yield ChoiceField::new('roles', 'form_registration.roles')
            ->allowMultipleChoices()
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setChoices($rolesChoices);

        yield ChoiceField::new('gender')
            ->setLabel('form_registration.gender_label')
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setRequired(true)
            ->setChoices([
                'form_registration.gender.men' => 0,
                'form_registration.gender.women' => 1,
            ]);
        yield DateField::new('dateOfBirth')
            ->setColumns('col-sm-6')
            ->setLabel('form_registration.date_of_birth')
            ->onlyOnForms();

        yield ImageField::new('avatar')
            ->setUploadDir("public/$avatarPath")
            ->setColumns('col-sm-6')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath($avatarPath)
            ->setLabel('user.avatar')
            ->onlyOnForms()
            ->setRequired(false);

        yield AssociationField::new('userDownloads')
            ->setColumns('col-sm-6')
            ->setLabel('Завантаження')->onlyOnDetail();

        yield FormField::addPanel('Додатково');
        yield AssociationField::new('userProperty')
            ->setColumns('col-sm-6')
            ->setLabel(false)
            ->setFormType(UserPropertyCrudFormType::class)
            ->onlyOnForms();
        yield TextField::new('region', 'Область')
            ->setColumns('col-sm-6');

    }

    public function configureFilters(Filters $filters): Filters
    {
        $rolesChoices = [];
        foreach (User::getAvailableRoles() as $role) {
            $rolesChoices[User::getNameRoles()[$role]] = $role;
        }

        return $filters
            ->add('region')
            ->add('createdAt')
            ->add(BooleanFilter::new('isBanned', 'Статус (Заблокований)'))
            ->add(ChoiceFilter::new('gender', 'Cтать')->setChoices([
                'Чоловік' => 0,
                'Жінка' => 1,
            ]))
            ->add(UserRoleFilter::new('roles', BooleanFilterType::class)
                ->setLabel('Ролі')
                ->setFormTypeOption('choices', $rolesChoices))
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'name'])
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
            ->setEntityLabelInPlural('admin.dashboard.user.list_title')
            ->setEntityLabelInSingular('admin.dashboard.user.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.user.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.user.edit_page_title')
            ->setFormThemes(
                [
                    '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                    '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                    '@EasyAdmin/crud/form_theme.html.twig',
                ]
            )
            ->showEntityActionsInlined()
            ->overrideTemplate('crud/detail', 'admin/user/detail.html.twig');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $user): void
    {
        $encodedPassword = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encodedPassword);

        parent::persistEntity($entityManager, $user);
    }

    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX,
                Action::EDIT)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('userExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('userExport')
                    ->addCssClass('btn btn-primary')
            )
            ->add(
                Crud::PAGE_EDIT,
                Action::new('userPasswordReset', 'Reset Pswd')
                    ->linkToCrudAction('userPasswordReset')
                    ->addCssClass('btn btn-primary')
            )
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX,
                Action::new(Action::DETAIL, '', 'fas fa-eye')
                    ->linkToCrudAction(Action::DETAIL))
            ->add(Crud::PAGE_DETAIL, Action::new('singleUserExport', 'Export')
                ->createAsGlobalAction()
                ->linkToCrudAction('singleUserExport')
                ->addCssClass('btn btn-primary'));

        $userId = $this->requestStack->getCurrentRequest()->query->get('entityId');
        if ($userId) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);
            if ($user && $user->IsVerified() == false) {
                $actions->add(Crud::PAGE_EDIT,
                    Action::new('userConfirmEmail', 'Підтвердити пошту')
                        ->linkToCrudAction('userConfirmEmail')
                        ->addCssClass('btn btn-primary')
                );
            }
        }

        return $actions;

    }

    /**
     * @param ExportService $exportService
     * @param Request $request
     *
     * @return void
     */
    public function userExport(ExportService $exportService, Request $request): void
    {
//       parent::createIndexQueryBuilder($this->getContext()->getSearch(),
////            $this->getContext()->getEntity(),
////            $this->getContext()->getEntity()->getFields(),
////           $filterCollection; //todo find where to get FilterCollection of EA3 - cant construct this service
        $query = $this->getDoctrine()
            ->getRepository('App:User')
            ->createQueryBuilder('u');

        if ($request->query->get('filters')) {
            $query = $this->buildQueryByEAFilters($query, $request->query->get('filters'));
        }
        $userList = $query->getQuery()->toIterable();
        $exportData = [];

        /** @var User $user */
        foreach ($userList as $user) {
            $exportData[] = $this->getGeneralExportUserData($user);
            $this->getDoctrine()->getManager()->clear();
        }
        if (!empty($exportData)) {
            $exportService->export('user', $exportData);
        }
    }

    private function buildQueryByEAFilters(QueryBuilder $queryBuilder, array $filters): QueryBuilder
    {
        foreach ($filters as $filter => $value) {

            switch ($filter) {
                case 'roles':
                    $queryBuilder->andWhere("u.roles LIKE '%\"{$value}\"%'")
                        ->setParameter('value', $value);
                    break;
                default:
                    if (is_array($value)) {
                        if ($value['comparison'] != 'between') {
                            $queryBuilder->andWhere("u.$filter {$value['comparison']} :value")
                                ->setParameter('value', $value['value']);
                        } else {
                            $queryBuilder->andWhere("u.$filter {$value['comparison']} :value AND :value2")
                                ->setParameters([
                                    'value' => $value['value'],
                                    'value2' => $value['value2']
                                ]);
                        }
                    } else {
                        $queryBuilder->andWhere("u.$filter = :value")
                            ->setParameter('value', $value);
                    }
                    break;
            }

        }
        return $queryBuilder;
    }

    private function getGeneralExportUserData(User $user): array
    {
        $roleList = [];
        foreach ($user->getRoles() as $role) {
            $roleList[] = $this->translator->trans("user.roles.$role");
        }

        $cropList = [];
        foreach ($user->getCrops() as $crop) {
            $cropList[] = $crop->getName();
        }

        $phoneList[] = $user->getPhone();
        foreach ($user->getPhones() as $phone) {
            $phoneList[] = $phone->getPhone();
        }

        $fullLocation = '';
        if ($user->getRegion()) $fullLocation .= $user->getRegion();
        if ($user->getDistrict()) $fullLocation .= ' ' . $user->getDistrict();
        if ($user->getLocality()) $fullLocation .= ' ' . $user->getLocality();


        return [
            'ID' => $user->getId(),
            "Ім'я" => $user->getName(),
            'Eлектронна пошта' => $user->getEmail(),
            'Пошта підтверджена' => $user->isVerified() === true ? 'Так' : 'Ні',
            'Телефон' => count($phoneList) > 1 ? implode(', ', $phoneList) : $user->getPhone(), //todo change this while userProperty main phone will be implemented properly
            'Ролі' => implode(', ', $roleList),
            'Стать' => $this->getGenderAsString($user),
            'Дата народження' => $user->getDateOfBirth(),
            'Область' => $fullLocation,
            'Діяльність у проекті' => $user->getActivity(),
            'Підписан на новини' => $user->getIsNewsSub() === true ? 'Так' : 'Ні',
            'Культури' => implode(', ', $cropList),
            'Статус' => $user->getIsBanned() === true ? 'Заблоковано' : 'Активний',
            'Дата створення' => $user->getCreatedAt(),
            'Дата редагування' => $user->getUpdatedAt(),
        ];

    }

    public function userPasswordReset(Request $request)
    {
        $userId = (int)$request->query->get('entityId');
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['id' => $userId]);

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem handling your password reset request - %s',
                $e->getReason()
            ));
            return $this->redirect($request->query->get('referrer'));
        }

        $email = (new TemplatedEmail())
            ->from(new Address('civentytest2@gmail.com', 'Agro Wiki Bot'))
            ->to($user->getEmail())
            ->subject($this->translator->trans('reset_password_email.subject'))
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($email);

        $this->addFlash('success', sprintf('Reset Password email has been sent.'));
        return $this->redirect($request->query->get('referrer'));
    }

    public function userConfirmEmail(Request $request): Response
    {
        $userId = (int)$request->query->get('entityId');
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['id' => $userId]);

        $this->processEmailConfirmationSending($user);
        $this->addFlash('success', sprintf('Лист з підтвердженням пошти відправленно'));
        return $this->redirect($request->query->get('referrer'));

    }

    private function processEmailConfirmationSending(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('email.info'), $this->getParameter('email.title')))
                ->to($user->getEmail())
                ->subject($this->translator->trans('form_registration.confirm_email'))
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }

    private function getGenderAsString(User $user): string
    {
        if ($user->getGender() === true) {
            return 'Жінка';
        } elseif ($user->getGender() === false) {
            return 'Чоловік';
        } else {
            return 'Не вказано';
        }
    }

    /**
     * @param EntityDto $entityDto
     * @param KeyValueStore $formOptions
     * @param AdminContext $context
     * @return FormBuilderInterface
     */

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addedLocationUserField($formBuilder);
    }

    /**
     * @param EntityDto $entityDto
     * @param KeyValueStore $formOptions
     * @param AdminContext $context
     * @return FormBuilderInterface
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addedLocationUserField($formBuilder);
    }

    public function deleteUserRole(AdminContext $adminContext)
    {
        $role = $adminContext->getRequest()->get('role');
        $user = $this->getDoctrine()->getRepository(User::class)
            ->find($adminContext->getRequest()->get('entityId'));
        if (!$user) {
            return;
        }
        $this->validateRequestedRole($role, false);
        $this->updateUserRole($user, $role, false);
        $this->updateRequestRole($user, $role, false);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', "Роль {$user->getNameRoles()[$role]}, була видалена у користувача {$user}");
        return $this->redirect($this->urlGenerator->setAction(Action::DETAIL)
            ->setController(UserCrudController::class)->removeReferrer()
            ->set('entityId', $user->getId()));
    }

    public function addUserRole(AdminContext $adminContext)
    {
        $role = $adminContext->getRequest()->get('role');
        if ($role === User::ROLE_SUPER_ADMIN && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return new Response('', 403);
        }
        $user = $this->getDoctrine()->getRepository(User::class)
            ->find($adminContext->getRequest()->get('entityId'));

        if (!$user) {
            return new Response('User not found', 404);
        }
        $this->validateRequestedRole($role, true);
        $this->updateUserRole($user, $role, true);
        $this->updateRequestRole($user, $role, true);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', "Rористувач {$user} отримав роль {$user->getNameRoles()[$role]}");
        return $this->redirect($this->urlGenerator->setAction(Action::DETAIL)
            ->setController(UserCrudController::class)->removeReferrer()
            ->set('entityId', $user->getId()));
    }

    public function singleUserExport(ExportService $exportService,Request $request)
    {
        $user = $this->getContext()->getEntity()->getInstance();
        $generalExportData = $this->getGeneralExportUserData($user);
        $specialUserExportData = $this->getSpecialUserExportData($user);
        $this->getDoctrine()->getManager()->clear();
        $exportData[] = array_merge($generalExportData, $specialUserExportData);
        if (!empty($exportData)) {
            $exportService->export('user', $exportData);
        }
    }

    private function getSpecialUserExportData(User $user): array
    {
        $userDownloads = $this->getUserDownloads($user);
        $itemRegistrations = $this->getUserItemRegistrations($user);
        $userApprovedCertificates = $this->getUserApprovedCertificates($user);
        $products = $this->getUserProducts($user);
        $kits = $this->getUserKits($user);
        $services = $this->getUserServices($user);
        $userProperty = $user->getUserProperty();
        return [
            'Назва Компанії' => $userProperty->getCompanyName() ? $userProperty->getCompanyName() : '',
            'Адреса' => $userProperty->getAddress() ? $userProperty->getAddress() : '',
            'Facebook' => $userProperty->getFacebookLink() ? $userProperty->getFacebookLink() : '',
            'Instagram' => $userProperty->getInstagramLink() ? $userProperty->getInstagramLink() : '',
            'Тип компанії' => $userProperty->getCompanyType() ? $userProperty->getCompanyType()->getName() : '',
            'Легальний тип компанії' => $userProperty->getLegalCompanyType() ? $userProperty->getLegalCompanyType()->getName() : '',
            'Про себе' => $userProperty->getDescription() ? $userProperty->getDescription() : '',
            'Відео про себе' => $userProperty->getDescriptionVideoLink() ? $userProperty->getDescriptionVideoLink() : '',
            'Завантаженні файли' => !empty($userDownloads) ? implode(', ', $userDownloads) : '',
            'Зареєстрован на' => !empty($itemRegistrations) ? implode(', ', $itemRegistrations) : '',
            'Сертифікати' => !empty($userApprovedCertificates) ? implode(', ', $userApprovedCertificates) : '',
            'Продукти' => !empty($products) ? implode(', ', $products) : '',
            'Послуги' => !empty($services) ? implode(', ', $services) : '',
            'Пропозиції' => !empty($kits) ? implode(', ', $kits) : '',
        ];
    }

    private function validateRequestedRole(string $role, bool $addingRole)
    {
        $addingRole ? $actionName = 'додати' : $actionName = 'видалити';
        if ($addingRole === true && $role === User::ROLE_SUPER_ADMIN) {
            return;
        }
        if ($role === User::ROLE_SUPER_ADMIN || $role === User::ROLE_USER) {
            $this->addFlash('error', "Ви не можете {$actionName} роль користувача чи суперадміна.");
            return $this->redirect($this->urlGenerator
                ->setController('UserCrudController')
                ->removeReferrer()
                ->setAction(Action::INDEX)
            );
        }
    }

    private
    function updateUserRole(User $user, string $role, bool $addingRole)
    {
        $roles = [];
        if ($addingRole === false) {
            foreach ($user->getRoles() as $currentRole) {
                if ($currentRole != $role) {
                    $roles[] = $currentRole;
                }
            }
        } else {
            $roles = $user->getRoles();
            if (!in_array($role, $user->getRoles())) {
                $roles[] = $role;
            }
        }

        $user->setRoles($roles);
    }

    private function updateRequestRole(User $user, string $role, bool $addingRole)
    {
        $flippedRequestedRoleNames = array_flip(User::$rolesInRequestRoles);
        if ($addingRole === false) {
            foreach ($user->getRequestRoles() as $requestRole) {
                if ($requestRole->getRole() === $flippedRequestedRoleNames[$role]) {
                    $user->removeRequestRole($requestRole);
                    $requestRoleInDb = $this->getDoctrine()->getRepository(RequestRole::class)->find($requestRole->getId());
                    $this->getDoctrine()->getManager()->remove($requestRoleInDb);
                }
            }
        } elseif ($addingRole === true && array_key_exists($role, $flippedRequestedRoleNames)) {
            $requestRole = new RequestRole();
            $requestRole->setCreatedAt(new \DateTime());
            $requestRole->setUpdatedAt(new \DateTime());
            $requestRole->setUser($user);
            $requestRole->setRole($flippedRequestedRoleNames[$role]);
            $requestRole->setIsApproved(true);
            $this->getDoctrine()->getManager()->persist($requestRole);
        }

    }

    private function getUserDownloads(User $user): array
    {
        $userDownloads = [];
        foreach ($user->getUserDownloads() as $userDownload) {
            $userDownload->getTitle()
                ? $userDownloads[] = $userDownload->getTitle()
                : $userDownloads[] = $userDownload->getFileName();
        }
        return $userDownloads;
    }

    private function getUserServices(User $user): array
    {
        $services = [];
        foreach ($user->getCommodityServices() as $service) {
            $services[] = $service->getTitle();
        }
        return $services;
    }

    private function getUserItemRegistrations(User $user): array
    {
        $itemRegistrations = [];
        foreach ($user->getItemRegistrations() as $itemRegistration) {
            $itemRegistrations[] = $itemRegistration->getItemType() . ' ' . $itemRegistration->getItemId()->getTitle();
        }
        return $itemRegistrations;
    }

    private function getUserApprovedCertificates(User $user): array
    {
        $userApprovedCertificates = [];
        foreach ($user->getUserProperty()->getUserCertificates() as $userCertificate) {
            if ($userCertificate->getApproved()) {
                $userCertificate->getIsEcology() ? $certificateTitle = 'Екологічний' . ' ' : '';
                $certificateTitle .= 'сертфікат' . ' ' . $userCertificate->getName();
                $userApprovedCertificates[] = $certificateTitle;
            }
        }
        return $userApprovedCertificates;
    }

    private function getUserProducts(User $user): array
    {
        $products = [];
        foreach ($user->getCommodityProducts() as $product) {
            $products[] = $product->getTitle();
        }
        return $products;
    }

    private function getUserKits(User $user): array
    {
        $kits = [];
        foreach ($user->getCommodityKits() as $kit) {
            $kits[] = $kit->getTitle();
        }
        return $kits;
    }
}
