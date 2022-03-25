<?php

namespace App\Command\FakeData;

use App\Entity\Market\Category;
use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SeedFakeServicesCommand extends AbstractSeedFakeCommand
{
    protected static $defaultName = 'app:seedFakeServices';
    protected static $defaultDescription = 'Seed 20k fake services in dev env';
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
        $output->writeln("Creating fake services...");
        $minMaxFakeUserId = $this->getMinMaxFakeUsersId();
        $now = new \DateTime();
        $kernelDir = $this->parameterBag->get('kernel.project_dir');
        $imagesDir = $this->parameterBag->get('app.entity.files.service');
        $files = scandir($kernelDir . $imagesDir);
        $defaultImage = $files[2];
        $categories = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->where('c.commodityType = :type')
            ->setParameter('type',Commodity::TYPE_SERVICE)
            ->andWhere('c.parent is not null')
            ->getQuery()->getResult();
        $this->dictionaryKeys = array_keys($this->getDictionaries()['crop']);
        for ($i = 0; $i < self::FAKE_DATA_LIMIT; $i++) {
            $service = new CommodityService();
            $userId = rand($minMaxFakeUserId[0], $minMaxFakeUserId[1]);
            $service->setUser(
                $this->entityManager->getRepository(User::class)->find($userId)
            );
            $service->setTitle($this->faker->firstName);
            $service->setUpdatedAt($now);
            $service->setCreatedAt($now);
            $service->setIsActive(!$this->faker->boolean(5));
            $service->setActiveFrom($this->faker->dateTime('-1 day'));
            $service->setActiveTo($this->faker->dateTimeBetween('+3 days', '+20 days'));
            $service->setPrice($this->faker->numberBetween(0, 10000));
            $service->setImage($defaultImage);
            $service->setDescription($this->faker->sentence(10, true));
            $category = $categories[rand(0, count($categories) - 1)];
            $service->setCategory($category);
            $this->setAttributeValues($category, $service);
            $this->entityManager->persist($service);
            $this->entityManager->flush();
            if ($i % 50 === 0) {
                $this->entityManager->clear(CommodityService::class);
            }
        }
        $output->writeln("$i new Fake Services successfully created.");
        return Command::SUCCESS;
    }
}
