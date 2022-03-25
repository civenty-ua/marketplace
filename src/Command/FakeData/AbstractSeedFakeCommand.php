<?php

namespace App\Command\FakeData;

use App\Entity\Market\Attribute;
use App\Entity\Market\Category;
use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityAttributeValue;
use App\Entity\Market\CommodityProduct;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSeedFakeCommand extends Command
{
    protected const FAKE_DATA_LIMIT = 5000;

    /** @var Generator */
    protected $faker;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        string                 $name = null
    )
    {
        $this->faker = Factory::create("en_EN");
        $this->entityManager = $entityManager;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }

    protected function checkEnv(OutputInterface $output)
    {
        if ($_SERVER['APP_ENV'] != 'dev') {
            $output->writeln('Application is not in dev environment. This command wont be executed.');
            return Command::FAILURE;
        }
    }

    protected function getMinMaxFakeUsersId()
    {
        $lastId = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->select('u.id')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getSingleScalarResult();

        $firstId = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.id NOT IN (:defaultUsersId)')
            ->setParameter('defaultUsersId', [1, 2])
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            $firstId,
            $lastId
        ];
    }

    protected function getDictionaries()
    {
        return $this->entityManager
            ->getRepository(Attribute::class)
            ->getAllDictionaries();
    }

    protected function setAttributeValues(Category $category, Commodity $product)
    {
        foreach ($category->getCategoryAttributesParameters() as $attributesParameter) {
            if ($attributesParameter->getRequired()) {
                $commodityAttributeValue = new CommodityAttributeValue();
                $commodityAttributeValue->setCommodity($product);
                $commodityAttributeValue->setAttribute($attributesParameter->getAttribute());
                switch ($attributesParameter->getAttribute()->getType()) {
                    case Attribute::TYPE_INT:
                        $commodityAttributeValue->setValue($this->faker->numberBetween(0, 20000));
                        break;
                    case Attribute::TYPE_STRING:
                        $commodityAttributeValue->setValue($this->faker->sentence(5));
                        break;
                    case Attribute::TYPE_LIST:
                        $attributeListValue = rand(0, count($attributesParameter->getCategoryAttributeListValues()) - 1);
                        $commodityAttributeValue->setValue($attributesParameter->getCategoryAttributeListValues()[$attributeListValue]->getId());
                        break;
                    case Attribute::TYPE_LIST_MULTIPLE:
                        $numberOfRandMultipleChoice = rand(0, count($attributesParameter->getCategoryAttributeListValues()) - 1);
                        $ids = [];
                        for ($i = 0; $i < $numberOfRandMultipleChoice; $i++) {
                            $ids[] = $attributesParameter->getCategoryAttributeListValues()[$i]->getId();
                        }
                        $commodityAttributeValue->setValue($ids);
                        break;
                    case Attribute::TYPE_DICTIONARY:
                        $numberOfIterations = rand(1,count($this->dictionaryKeys)-10);
                        $ids = [];
                        for ($i = 0; $i < $numberOfIterations; $i++) {
                            $ids[] = $this->dictionaryKeys[$i];
                        }
                        $commodityAttributeValue->setValue($ids);
                        break;
                }
                $this->entityManager->persist($commodityAttributeValue);
                $product->addCommodityAttributeValue($commodityAttributeValue);
            }
        }
    }
}
