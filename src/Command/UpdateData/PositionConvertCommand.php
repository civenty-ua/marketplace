<?php

namespace App\Command\UpdateData;

use App\Entity\Expert;
use App\Entity\ExpertType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PositionConvertCommand extends Command
{
    protected static $defaultName = 'app:position-convert';
    protected static $defaultDescription = 'Add a short description for your command';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $experts = $this->entityManager->getRepository(Expert::class)->findAll();

        foreach ($experts as $expert) {
            $arrExpertType = [];

            foreach ($expert->getExpertTypes() as $expertType) {
                    $arrExpertType[] = $expertType->getName();
            }

            $positions =  implode('<br>', $arrExpertType);
            $expert->setPosition($positions);
            $this->entityManager->persist($expert);
            $this->entityManager->flush();
        }

        $io->success('Position converted to text');

        return Command::SUCCESS;
    }
}
