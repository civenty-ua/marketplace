<?php

namespace App\Service\MailSender\Provider;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\{
    Exception\TransportExceptionInterface,
    MailerInterface,
};
use Symfony\Component\Mime\Address;

class SwiftMailerProvider implements MailSenderProviderInterface
{
    private ParameterBagInterface $parameterBag;
    private LoggerInterface $logger;
    private MailerInterface $mailer;
    private string $emailInfo;
    private string $emailTitle;

    public function __construct(
        ParameterBagInterface $parameterBag,
        LoggerInterface $logger,
        MailerInterface $mailer
    ) {
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->emailInfo = $this->parameterBag->get('email.info');
        $this->emailTitle = $this->parameterBag->get('email.title');
    }
    /**
     * @inheritDoc
     */
    public function send(string $email, string $title, string $template, array $data = []): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->emailInfo, $this->emailTitle))
                ->to($email)
                ->subject($title)
                ->htmlTemplate($template)
                ->context($data);

            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $exception) {
            $message    = $exception->getMessage();
            $code       = $exception->getCode();
            $file       = $exception->getFile();
            $line       = $exception->getLine();
            $errorFull  = "SwiftMailerProvider: $message $code $file $line";

            $this->logger->error($errorFull);
            return false;
        }
    }
}