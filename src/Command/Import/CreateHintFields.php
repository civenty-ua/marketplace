<?php

namespace App\Command\Import;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\TextType;
use App\Entity\TextBlocks;
use Doctrine\ORM\EntityManagerInterface;

class CreateHintFields extends Command
{
    protected static $defaultName = 'app:import:createHintFields';

    private $TextTypeNames = ['Підказки щодо ролей у профілі користувача'];
    private $TextTypeBlocks = [
        ['buyer', 'text1', 'Цей текст про роль користувача/покупця'],
        ['wholesale-bayer', 'text2', 'Цей текст про роль оптового покупця'],
        ['salesman', 'text3', 'Цей текст про роль продавця'],
        ['service-provider', 'text4', 'Цей текст про роль постачальника послуг']
    ];

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Hint fields Creator',
            '============',
            '',
        ]);
        $q = $this->entityManager->getRepository(TextBlocks::class)->createQueryBuilder('tb');
        $q->delete()->getQuery()->execute();
        $q = $this->entityManager->getRepository(TextType::class)->createQueryBuilder('tt');
        $q->delete()->getQuery()->execute();


        $textName = new TextType();
        $textName->setName($this->TextTypeNames[0]);
        $this->entityManager->persist($textName);

        $this->entityManager->flush();

        for ($i = 0; $i < 4; $i++) {
            $textBlock = new TextBlocks();
            $textBlock->setTextTypeId($textName);
            $textBlock->setSymbolCode($this->TextTypeBlocks[$i][0]);
            $textBlock->setText($this->TextTypeBlocks[$i][1]);
            $textBlock->setTextDescrtiption($this->TextTypeBlocks[$i][2]);
            $this->entityManager->persist($textBlock);
        }
        $this->entityManager->flush();

        $output->writeln([
            'Done.'
        ]);
        return Command::SUCCESS;
    }
}