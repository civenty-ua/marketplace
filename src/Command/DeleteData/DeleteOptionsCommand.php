<?php

namespace App\Command\DeleteData;

use App\Entity\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOptionsCommand extends Command
{
    protected static $defaultName = 'app:delete-options';
    protected static string $defaultDescription = 'Delete option in admin';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existOption = $this->checkExistsOptions();
        foreach ($existOption as $option) {
            $this->entityManager->remove($option);
        }
        $this->entityManager->flush();
        $io->success('Counter options has been created!');

        return Command::SUCCESS;
    }

    private function checkExistsOptions():array
    {
        $arr = [];
        /**
         * @var Options[] $options
         */
        $options = $this->entityManager->getRepository(Options::class)->findBy([
            'code' => [
                'market_products_description_uk',
                'market_products_description_en',
                'market_kits_description_uk',
                'market_kits_description_en',
            ]
        ]);

        foreach ($options as $option) {
            $arr[$option->getCode()] = $option;
        }

        return $arr;
    }
}