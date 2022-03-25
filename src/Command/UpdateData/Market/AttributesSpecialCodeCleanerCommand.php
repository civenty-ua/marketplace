<?php
declare(strict_types=1);

namespace App\Command\UpdateData\Market;

use Throwable;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
    Helper\ProgressBar,
};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Market\Attribute;
/**
 * Import services categories command.
 */
class AttributesSpecialCodeCleanerCommand extends Command
{
    protected static $defaultName = 'app:update:market:attribute-special-code-cleaner';

    protected EntityManagerInterface $entityManager;
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Application data update: market attributes special code cleaner')
            ->setHelp('Run Application data update process for market attributes, '
                .'find unregistered special codes and clean them');
    }
    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            'success'   => 0,
            'failed'    => 0,
        ];

        $output->writeln('Required data searching...');
        $attributes = $this->findAttributesWithUnregisteredSpecialCodes();

        if (count($attributes) === 0) {
            $output->writeln('No required data was found...');
            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln("Required data processing...\n");

        foreach ($progressBar->iterate($attributes) as $attribute) {
            try {
                $this->cleanAttributeSpecialCode($attribute);
                $outputData['success']++;
            } catch (Throwable $exception) {
                $outputData['failed']++;
                $output->writeln("\nProcess error: attribute {$attribute->getId()}, {$exception->getMessage()}");
            }
        }

        $output->writeln("\nData processing finished");
        $output->writeln("Attributes cleaned: {$outputData['success']}");
        $output->writeln("Attributes cleaning process failed: {$outputData['failed']}");

        return Command::SUCCESS;
    }
    /**
     * Try to find attributes with unknown special code.
     *
     * @return Attribute[] Attributes set.
     */
    private function findAttributesWithUnregisteredSpecialCodes(): iterable
    {
        $registeredCodes    = Attribute::getAvailableSpecialCodes();
        $alias              = 'attributes';
        $queryBuilder       = $this->entityManager
            ->getRepository(Attribute::class)
            ->createQueryBuilder($alias);

        return $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->isNotNull("$alias.code")
            )
            ->andWhere(
                $queryBuilder->expr()->notIn("$alias.code", $registeredCodes)
            )
            ->getQuery()
            ->getResult();
    }
    /**
     * Try to clean attribute special code.
     *
     * @param Attribute $attribute Attribute.
     *
     * @return void
     */
    private function cleanAttributeSpecialCode(Attribute $attribute): void
    {
        $attribute->setCode(null);
        $this->entityManager->flush();
    }
}
