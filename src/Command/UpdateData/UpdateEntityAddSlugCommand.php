<?php

namespace App\Command\UpdateData;

use App\Entity\Market\Commodity;
use App\Repository\Market\CommodityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateEntityAddSlugCommand extends Command
{
    protected static $defaultName = 'app:updateEntity:addSlug';
    protected static $defaultDescription = 'Command for add slug to all commodities';

    protected $entityManager;
    public function __construct(string $name = null, EntityManagerInterface $em)
    {
        $this->entityManager = $em;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commodities = $this->entityManager->getRepository(Commodity::class)
            ->findAll();

        foreach ($commodities as $commodity)
        {
            /**
             * @var Commodity $commodity
             */
            $commodity->setSlug($commodity->getTitle().' '.$commodity->getId());
        }
        $this->entityManager->flush();
        $io->success('All done!');

        return Command::SUCCESS;
    }
}
