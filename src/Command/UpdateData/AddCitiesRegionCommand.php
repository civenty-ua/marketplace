<?php

namespace App\Command\UpdateData;

use App\Entity\Region;
use App\Entity\RegionTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddCitiesRegionCommand extends Command
{
    private const REGIONS = [
        [
            'uk' => 'АР Крим',
            'en' => 'Autonomous Republic of Crimea',
            'code' => 'crimea',
            'sort' => 1
        ],
        [
            'uk' => 'Черкаська',
            'en' => 'Cherkasy',
            'code' => 'cherkasy',
            'sort' => 1
        ],
        [
            'uk' => 'Чернігівська',
            'en' => 'Chernihiv',
            'code' => 'chernihiv',
            'sort' => 1
        ],
        [
            'uk' => 'Чернівецька',
            'en' => 'Chernivtsi',
            'code' => 'chernivtsi',
            'sort' => 1
        ],
        [
            'uk' => 'Дніпропетровська',
            'en' => 'Dnipropetrovsk',
            'code' => 'dnipropetrovsk',
            'sort' => 1
        ],
        [
            'uk' => 'Донецька',
            'en' => 'Donetsk',
            'code' => 'donetsk',
            'sort' => 1
        ],
        [
            'uk' => 'Івано-Франківська',
            'en' => 'Ivano-Frankivsk',
            'code' => 'ivano-frankivsk',
            'sort' => 1
        ],
        [
            'uk' => 'Харківська',
            'en' => 'Kharkiv',
            'code' => 'kharkiv',
            'sort' => 1
        ],
        [
            'uk'    => 'Херсонська',
            'en'    => 'Kherson',
            'code'  => 'kherson',
            'sort' => 1
        ],
        [
            'uk' => 'Хмельницька',
            'en' => 'Khmelnytsk ',
            'code' => 'khmelnytsk ',
            'sort' => 1
        ],
        [
            'uk' => 'Кіровоградська',
            'en' => 'Kirovohrad ',
            'code' => 'kirovohrad ',
            'sort' => 1
        ],
        [
            'uk' => 'Київська',
            'en' => 'Kyiv',
            'code' => 'kyiv',
            'sort' => 1
        ],
        [
            'uk' => 'Луганська',
            'en' => 'Luhansk',
            'code' => 'luhansk',
            'sort' => 1
        ],
        [
            'uk' => 'Львівська',
            'en' => 'Lviv',
            'code'=> 'lviv',
            'sort' => 1
        ],
        [
            'uk'    => 'Миколаївська',
            'en'    => 'Mykolaiv',
            'code'  => 'mykolaiv',
            'sort' => 1
        ],
        [
            'uk'    => 'Одеська',
            'en'    => 'Odesa',
            'code'  => 'odesa',
            'sort' => 1
        ],
        [
            'uk' => 'Полтавська',
            'en' => 'Poltava',
            'code' => 'poltava',
            'sort' => 1
        ],
        [
            'uk' => 'Рівненська',
            'en' => 'Rivne',
            'code' => 'rivne',
            'sort' => 1
        ],
        [
            'uk' => 'Сумська',
            'en' => 'Sumy',
            'code' => 'sumy',
            'sort' => 1
        ],
        [
            'uk' => 'Тернопільська',
            'en' => 'Ternopil',
            'code' => 'ternopil',
            'sort' => 1
        ],
        [
            'uk' => 'Вінницька',
            'en' => 'Vinnytsia',
            'code' => 'vinnytsia',
            'sort' => 1
        ],
        [
            'uk' => 'Волинська',
            'en' => 'Volyn',
            'code' => 'volyn',
            'sort' => 1
        ],
        [
            'uk' => 'Закарпатська',
            'en' => 'Zakarpattia',
            'code' => 'zakarpattia',
            'sort' => 1
        ],
        [
            'uk'    => 'Запорізька',
            'en'    => 'Zaporizhzhia',
            'code'  => 'zapogizdja',
            'sort' => 1
        ],
        [
            'code'=> 'zhytomyr',
            'uk' => 'Житомирська',
            'en' => 'Zhytomyr',
            'sort' => 1
        ],
        [
            'code' => 'kyiv-city',
            'uk' => 'місто Київ',
            'en' => 'Kyiv city',
            'sort' => 2
        ],
        [
            'code' => 'sevastopol-city',
            'uk' => 'місто Севастополь',
            'en' => 'Sevastopol city',
            'sort' => 3
        ],
    ];
    protected static $defaultName = 'app:add-cities-region';
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
        $existRegion = $this->checkRegionExist();

        foreach (self::REGIONS as $region) {
            extract($region);
            if (isset($existRegion[$uk]))
            {
                $updateRegion =  $existRegion[$uk];
                $updateRegion->setSort($region['sort'] ?? null);
                $updateRegion->setCode($region['code'] ?? null);
                $this->entityManager->persist($updateRegion);
                $this->entityManager->flush();
            } else {
                $reg = new Region();
                $reg->setCode($region['code'] ?? null);
                $reg->setSort($region['sort'] ?? null);
                $regionEn = new RegionTranslation();
                $regionEn->setName($region['en']);
                $regionEn->setLocale('en');
                $regionEn->setTranslatable($reg);
                $regionUk = new RegionTranslation();
                $regionUk->setName($region['uk']);
                $regionUk->setLocale('uk');
                $regionUk->setTranslatable($reg);
                $this->entityManager->persist($reg);
                $this->entityManager->persist($regionEn);
                $this->entityManager->persist($regionUk);
                $this->entityManager->flush();
            }

        }

        $io->success('Cities added');

        return Command::SUCCESS;
    }

    private function checkRegionExist(): array
    {
        $reg = $this->entityManager->getRepository(Region::class)->findAll();

        foreach ($reg as $data) {
            $reg[$data->getName()] = $data;
        }

        return $reg;
    }
}
