<?php


namespace App\DataFixtures;

use SplFileInfo;
use App\Entity\Contact;
use App\Entity\ContactTranslation;
use App\Service\FileManager\FileManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContactFixtures extends Fixture implements FixtureGroupInterface
{
    private const SMAT_DIR = 'public' . DIRECTORY_SEPARATOR .
    'images' . DIRECTORY_SEPARATOR . 'smat';
    private const CONTACT_DIR = 'public' . DIRECTORY_SEPARATOR .
    'upload' . DIRECTORY_SEPARATOR . 'contact';

    protected FileManager           $fileManager;
    protected ParameterBagInterface $parameter;

    public function __construct(
        FileManager             $fileManager,
        ParameterBagInterface   $parameter
    ) {
        $this->fileManager  = $fileManager;
        $this->parameter    = $parameter;
    }

    public function load(ObjectManager $manager)
    {
        $contacts = [
            [
                'image' => 'region-zaporizha_60x60.svg',
                'email' => 'ipod.tdatu@gmail.com',
                'site' => 'http://ikcat.org',
                'phone' => '067-613-72-58',
                'cellPhone' => '098-217-63-66',
                'uk' => [
                    'title' => 'ІКЦ «Агро-Таврія»',
                    'address' => 'Мелітополь, пр. Б.Хмельницкого, 20, ТДАТУ',
                    'head' => 'Подшивалов Геннадій',
                    'position' => 'ГО «ВУ Рада Жінок-Фермерів України»',
                    'fullname' => 'Спеціаліст з розвитку ринку - Станіслав Глущенко',
                ],
                'en' => [
                    'title' => 'ICC "Agro-Tavria"',
                    'address' => 'Melitopol, B. Khmelnytsky Ave., 20, TSATU',
                    'head' => 'Podshivalov Gennady',
                    'position' => 'NGO "VU Council of Women Farmers of Ukraine"',
                    'fullname' => 'Market development specialist - Stanislav Glushchenko',
                ],
            ],
            [
                'image' => 'region-mukolaiv_60x60.svg',
                'email' => 'irina.kuprievich@gmail.com',
                'site' => 'http://laskabf.org',
                'phone' => '0675147080, 0933853608',
                'cellPhone' => null,
                'uk' => [
                    'title' => 'БФ ЛАСКА',
                    'address' => 'Миколаїв, вул. Бузніка 5/1, офіс 206',
                    'head' => 'Купрієвич Ірина',
                    'position' => null,
                    'fullname' => null,
                ],
                'en' => [
                    'title' => 'LASKA Charitable Foundation',
                    'address' => 'Mykolaiv, street Buznika 5/1, office 206',
                    'head' => 'Kuprievich Irina',
                    'position' => null,
                    'fullname' => null,
                ],
            ],
            [
                'image' => 'region-odessa_60x60.svg',
                'email' => 'oracs@ukr.net',
                'site' => 'http://oracs.org',
                'phone' => '048-726-95-59',
                'cellPhone' => '380675571528',
                'uk' => [
                    'title' => 'ІКЦ «Агро-Таврія»',
                    'address' => 'Одеса, вул. Преображенська 34, оф. 355',
                    'head' => 'Мельник Євген',
                    'position' => 'ГО «ВУ Рада Жінок-Фермерів України»',
                    'fullname' => 'Голова правління - Людмила Клєбанова',
                ],
                'en' => [
                    'title' => 'ICC "Agro-Tavria"',
                    'address' => 'Odessa, street Preobrazhenskaya 34, office 355',
                    'head' => 'Melnik Eugene',
                    'position' => 'NGO "VU Council of Women Farmers of Ukraine"',
                    'fullname' => 'Chairman of the Board - Lyudmila Klebanova',
                ],
            ],
            [
                'image' => 'region-herson_60x60.svg',
                'email' => 'elena@ndovira.com',
                'site' => 'https://hgozt.org',
                'phone' => '0675531322',
                'cellPhone' => '380953171910',
                'uk' => [
                    'title' => 'ГО «Земля Таврії»',
                    'address' => 'Херсон, вул. В. Гошкевича, 45',
                    'head' => 'Синюк Олена',
                    'position' => 'ГО «ВУ Рада Жінок-Фермерів України»',
                    'fullname' => 'Голова Херсонського осередку - Віра Машинська',
                ],
                'en' => [
                    'title' => 'NGO "Land of Tavria"',
                    'address' => 'Kherson, street V. Goshkevich, 45',
                    'head' => 'Sinyuk Olena',
                    'position' => 'NGO "VU Council of Women Farmers of Ukraine"',
                    'fullname' => 'The head of the Kherson branch - Vira Mashinska',
                ],
            ]
        ];

        $projectDirectoryPath   = $this->parameter->get('kernel.project_dir');
        $smatDirectoryPath      = $projectDirectoryPath.DIRECTORY_SEPARATOR.self::SMAT_DIR;
        $smatDirectory          = new SplFileInfo($smatDirectoryPath);
        $contactsDirectoryPath  = $projectDirectoryPath.DIRECTORY_SEPARATOR.self::CONTACT_DIR;
        $contactsDirectory      = new SplFileInfo($contactsDirectoryPath);

        if (!$contactsDirectory->isDir()) {
            mkdir($contactsDirectory->getPathname(), 0755);
        }

        foreach ($contacts as $item) {
            $reg = new Contact();
            $contactEn = new ContactTranslation();
            $contactEn->setTitle($item['en']['title']);
            $contactEn->setAddress($item['en']['address']);
            $contactEn->setHead($item['en']['head']);
            if ($item['en']['position']) $contactEn->setPosition($item['en']['position']);
            if ($item['en']['fullname']) $contactEn->setFullname($item['en']['fullname']);
            $contactEn->setLocale('en');
            $contactEn->setTranslatable($reg);
            $contactUk = new ContactTranslation();
            $contactUk->setTitle($item['uk']['title']);
            $contactUk->setAddress($item['uk']['address']);
            $contactUk->setHead($item['uk']['head']);
            if ($item['uk']['position']) $contactUk->setPosition($item['uk']['position']);
            if ($item['uk']['fullname']) $contactUk->setFullname($item['uk']['fullname']);
            $contactUk->setLocale('uk');
            $contactUk->setTranslatable($reg);

            if ($contactsDirectory->isDir()) {
                $fileCurrentPath    = $smatDirectory->getPathname().DIRECTORY_SEPARATOR.$item['image'];
                $fileNewPath        = $contactsDirectory->getPathname().DIRECTORY_SEPARATOR.$item['image'];

                copy($fileCurrentPath, $fileNewPath);
            }

            $reg->setImage($item['image']);
            $reg->setEmail($item['email']);
            $reg->setSite($item['site']);
            $reg->setPhone($item['phone']);
            if ($item['cellPhone']) $reg->setCellPhone($item['cellPhone']);
            $manager->persist($reg);
            $manager->persist($contactEn);
            $manager->persist($contactUk);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}