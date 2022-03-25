<?php

namespace App\Command\UpdateData;

use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Partner;
use App\Entity\Region;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class RegionAndCityUpdateCommand extends Command
{
    protected static $defaultName = 'app:region:update';

    protected const ITEM_CREATED = 'created';
    protected const ITEM_UPDATED = 'updated';
    protected const ITEM_FAILED = 'failed';

    protected ParameterBagInterface $parameter;

    private const FILE_ALLOWED_EXTENSIONS = [
        'json'
    ];

    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameter
    )
    {
        $this->entityManager = $entityManager;
        $this->parameter = $parameter;
        parent::__construct();
    }


    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Region, district and locality update')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'path to source file, absolute or from application root ' .
                '(for example: src/DataFixtures/sources/Import/Article/article.xlsx)',
                'src/DataFixtures/sources/location_ua.json'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            self::ITEM_CREATED => 0,
            self::ITEM_UPDATED => 0,
            self::ITEM_FAILED => 0,
        ];

        try {
            $filePath = $input->getArgument('file');
            if (!isset($filePath) or empty($filePath)) {
                $filePath = 'src/DataFixtures/sources/location_ua.json';
            }

            $output->writeln('Required data initializing...');
            $output->writeln('File searching...');
            $file = $this->findDataProviderFile($filePath);

            $data = json_decode($file, true);
            $states = [];

            foreach ($data as $item) {
                $all[$item['id']] = $item;
            }

            foreach ($data as $item) {
                if ($item['parent_id'] == 1) {
                    $states[$item['id']] = $item;

                }
            }
            $districts = [];
            foreach ($data as $item) {
                if (isset($states[$item['parent_id']])) {
                    $states[$item['parent_id']]['children'][$item['id']] = $item;
                    $districts[$item['id']] = $item;
                }
            }
            $localities = [];
            foreach ($data as $item) {
                if (isset($districts[$item['parent_id']])) {
                    $states[$districts[$item['parent_id']]['parent_id']]['children'][$districts[$item['parent_id']]['id']]['children'][$item['id']] = $item;
                    $localities[$item['id']] = $item;
                } elseif (isset($all[$item['parent_id']]) and isset($districts[$all[$item['parent_id']]['parent_id']])) {
                    $districtId = $all[$item['parent_id']]['parent_id'];
                    $stateId = $districts[$all[$item['parent_id']]['parent_id']]['parent_id'];
                    $states[$stateId]['children'][$districtId]['children'][$item['id']] = $item;
                    $localities[$item['id']] = $item;
                }
            }
            $stateDB = $this->entityManager->getRepository(Region::class)->getAllRegion();
            $allState = $this->entityManager->getRepository(Region::class)->getAllRegionObject();
            foreach ($states as &$state) {
                if (isset($stateDB[$state['name']['uk']])) {
                    $state['id_db'] = $stateDB[$state['name']['uk']]['id'];

                }
                if ($state['name']['uk'] == 'Автономна Республіка Крим') {
                    $state['id_db'] = $stateDB['АР Крим']['id'];
                }
                if (isset($state['children'])) {
                    foreach ($state['children'] as $discrit) {
                        $disc = new District();
                        $disc->setRegion($allState[$state['id_db']]);
                        $disc->setName($discrit['name']['uk']);
                        $this->entityManager->persist($disc);
                        if (isset($discrit['children'])) {
                            foreach ($discrit['children'] as $locality) {
                                $local = new Locality();
                                $local->setName($locality['name']['uk']);
                                $local->setDistrict($disc);
                                $this->entityManager->persist($local);
                            }
                        }
                    }
                }
            }
            $this->entityManager->flush();

        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * @param string $value
     * @return false|string
     */
    protected function findDataProviderFile(string $value)
    {
        $filePath = $value === DIRECTORY_SEPARATOR
            ? $value
            : $this->parameter->get('kernel.project_dir') . DIRECTORY_SEPARATOR . $value;
        $file = file_get_contents($filePath);
        return $file;
    }


    public function processItem($item)
    {
        $slugify = new Slugify();

        $slug = $slugify->slugify($item->getTranslations()['uk']->getName());
        $slugCount = count($this->entityManager->getRepository(Partner::class)->findAllBySlug($slug));
        if ($slugCount > 0) $slug = $slug . '-' . $slugCount;
        $item->setSlug($slug);
        $this->entityManager->flush();

        return self::ITEM_UPDATED;
    }
}