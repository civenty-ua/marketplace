<?php

namespace App\Command\UpdateData;

use App\Entity\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionMenuCatalogSupportCommand extends Command
{

    private const  OPTION_STRUCTURE = [
            'code' 			=> 'menu_catalog_support_program',
            'value' 		=> 'https://uhbdp.org/prohramy-pidtrymky-ahrosektoru-2021/',
            'description' 	=> 'menu.catalog.support_program.description',
        ];

    protected static $defaultName = 'app:create-catalog-support-program';
    protected static $defaultDescription = 'Add a short description for your command';
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->entityManager->getRepository(Options::class)->findOneBy([
            'code' => 'menu_catalog_support_program'
        ])) {
            $io->error('Options already exists');
            return Command::FAILURE;
        }

        extract((self::OPTION_STRUCTURE));

        $options = new Options();

        $options->setCode($code);
        $options->setValue($value);
        $options->setDescription($this->translator->trans($description));

        $this->entityManager->persist($options);
        $this->entityManager->flush();

        $io->success('Options has been created!');
        return Command::SUCCESS;

    }
}
