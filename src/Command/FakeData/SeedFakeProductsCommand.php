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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SeedFakeProductsCommand extends AbstractSeedFakeCommand
{
    protected static $defaultName = 'app:seedFakeProducts';
    protected static $defaultDescription = 'Seed 5k fake products in dev env';
    protected ParameterBagInterface $parameterBag;
    protected array $dictionaryKeys;

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag, string $name = null)
    {
        $this->parameterBag = $parameterBag;
        parent::__construct($entityManager, $name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkEnv($output);
        $output->writeln("Creating fake products...");
        $minMaxFakeUserId = $this->getMinMaxFakeUsersId();
        $now = new \DateTime();
        $kernelDir = $this->parameterBag->get('kernel.project_dir');
        $imagesDir = $this->parameterBag->get('app.entity.files.product');
        $files = scandir($kernelDir . $imagesDir);
        $defaultImage = $files[2];
        //todo implement ProductCategory Attributes workflow
        $categories = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
        ->where('c.commodityType = :type')
            ->setParameter('type',Commodity::TYPE_PRODUCT)
            ->andWhere('c.parent is not null')
            ->getQuery()->getResult();
        $this->dictionaryKeys = array_keys($this->getDictionaries()['crop']);


        for ($i = 0; $i < self::FAKE_DATA_LIMIT; $i++) {
            $product = new CommodityProduct();
            $userId = rand($minMaxFakeUserId[0], $minMaxFakeUserId[1]);
            $product->setUser(
                $this->entityManager->getRepository(User::class)->find($userId)
            );
            $product->setTitle($this->faker->firstName);
            $product->setUpdatedAt($now);
            $product->setCreatedAt($now);
            $product->setIsActive(!$this->faker->boolean(5));
            $product->setActiveFrom($this->faker->dateTime('-1 day'));
            $product->setActiveTo($this->faker->dateTimeBetween('+3 days', '+20 days'));
            $product->setPrice($this->faker->numberBetween(0, 10000));
            $product->setImage($defaultImage);
            $product->setDescription($this->faker->sentence(10, true));
            $category = $categories[rand(0, count($categories) - 1)];
            $product->setCategory($category);
            $this->setAttributeValues($category, $product);

            if ($this->faker->boolean(70)) {
                $product->setType(CommodityProduct::TYPE_SELL);
            } else {
                $product->setType(CommodityProduct::TYPE_BUY);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            if ($i % 50 === 0) {
                $this->entityManager->clear(CommodityProduct::class);
            }
        }
        $output->writeln("$i new Fake Products successfully created.");
        return Command::SUCCESS;
    }
}
