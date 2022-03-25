<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\{
    Exception\RuntimeException as FormSubmitException,
    FormInterface,
    FormFactoryInterface,
};
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\{
    Exception\TransportExceptionInterface as MailerTransportException,
    MailerInterface,
};
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use App\Form\ContactUsForm;
use App\Entity\{
    CompanyMessages,
    User,
};
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ContactUsForm handler service.
 */
class ContactUsFormHandler
{
    private const RECAPTCHA_SCORE_VALID = 0.7;

    private FormFactoryInterface $formFactory;

    private Recaptcha3Validator $recaptcha3Validator;

    private EntityManagerInterface $entityManager;

    private MailerInterface $mailer;

    private ?User $user;

    public function __construct(
        FormFactoryInterface $formFactory,
        Recaptcha3Validator $recaptcha3Validator,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Security $security
    ) {
        $this->formFactory = $formFactory;
        $this->recaptcha3Validator = $recaptcha3Validator;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->user = $security->getUser();
    }

    /**
     * Build empty entity and get it
     *
     * @return CompanyMessages Entity.
     */
    public function buildEntity(): CompanyMessages
    {
        $companyMessage = new CompanyMessages();

        if ($this->user) {
            $companyMessage->setName($this->user->getName());
            $companyMessage->setEmail($this->user->getEmail());
            $companyMessage->setPhone($this->user->getPhone());
        }

        return $companyMessage;
    }

    /**
     * Build form and get it.
     *
     * @param CompanyMessages|null $entity Entity.
     *
     * @return FormInterface Form.
     */
    public function buildForm(?CompanyMessages $entity = null): FormInterface
    {
        return $this->formFactory->create(ContactUsForm::class, $entity);
    }

    /**
     * Handle form submit.
     *
     * @param Request $request Request.
     * @param FormInterface $form Form.
     * @param CompanyMessages $entity Entity.
     * @param TranslatorInterface $translator Translator.
     *
     * @return void
     * @throws FormSubmitException Form submit error.
     */
    public function handleFormSubmit(
        Request $request,
        FormInterface $form,
        CompanyMessages $entity,
        TranslatorInterface $translator
    ): void {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new FormSubmitException('form submit error');
        }

        if ($this->recaptcha3Validator->getLastResponse()->getScore() < self::RECAPTCHA_SCORE_VALID) {
            throw new FormSubmitException('recaptcha score is not enough');
        }

        $email = $form->get('email')->getData();
        $message = $form->get('message')->getData();
        $emailTemplate = $this->buildEmailLetter($email, $message, $translator);

        try {
            $this->mailer->send($emailTemplate);
        } catch (MailerTransportException $exception) {

        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Build email and get it.
     *
     * @param string $email Email.
     * @param string $message Message.
     *
     * @return TemplatedEmail Letter.
     */
    private function buildEmailLetter(string $email, string $message, TranslatorInterface $translator): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address('support@agro.com', $translator->trans('contact_us_email.title')))
            ->to('info@uhbdp.org')
            ->subject($translator->trans('contact_us_email.subject') . $email)
            ->htmlTemplate('company-messages/email.html.twig')
            ->context([
                'message' => $message,
            ]);
    }
}
