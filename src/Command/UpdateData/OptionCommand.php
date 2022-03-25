<?php

namespace App\Command\UpdateData;

use App\Entity\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionCommand extends Command
{
    private const OPTION_STRUCTURE = [
        [
            'code' 			=> 'index_page_counter_webinar_value',
            'value' 		=> 0,
            'description' 	=> 'options.index_page_counter_webinar_value.description',
        ],
        [
            'code' 			=> 'index_page_counter_course_value',
            'value' 		=> 0,
            'description' 	=> 'options.index_page_counter_course_value.description',
        ],
        [
            'code' 			=> 'index_page_counter_commodity_value',
            'value' 		=> 0,
            'description' 	=> 'options.index_page_counter_commodity_value.description',
        ],
        [
            'code' 			=> 'index_page_counter_buyer_value',
            'value' 		=> 0,
            'description' 	=> 'options.index_page_counter_buyer_value.description',
        ],
        [
            'code' 			=> 'index_page_counter_webinar_description_uk',
            'value' 		=> 'вебінарів',
            'description' 	=> 'options.index_page_counter_webinar_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_webinar_description_en',
            'value' 		=> 'webinars',
            'description' 	=> 'options.index_page_counter_webinar_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_course_description_uk',
            'value' 		=> 'курсів',
            'description' 	=> 'options.index_page_counter_course_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_course_description_en',
            'value' 		=> 'courses',
            'description' 	=> 'options.index_page_counter_course_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_commodity_description_uk',
            'value' 		=> 'товарів',
            'description' 	=> 'options.index_page_counter_commodity_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_commodity_description_en',
            'value' 		=> 'goods',
            'description' 	=> 'options.index_page_counter_commodity_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_buyer_description_uk',
            'value' 		=> 'закупівельників',
            'description' 	=> 'options.index_page_counter_buyer_title.description',
        ],
        [
            'code' 			=> 'index_page_counter_buyer_description_en',
            'value' 		=> 'buyers',
            'description' 	=> 'options.index_page_counter_buyer_title.description',
        ],
        [
            'code'          => 'market_goods_description_uk',
            'value' 		=> 'Опис для товарів',
            'description' 	=> 'options.market_products.description',
        ],
        [
            'code'          => 'market_goods_description_en',
            'value' 		=> 'Description for goods',
            'description' 	=> 'options.market_products.description',
        ],
        [
            'code'          => 'market_services_description_uk',
            'value' 		=> 'Опис для послуг',
            'description' 	=> 'options.market_services.description',
        ],
        [
            'code'          => 'market_services_description_en',
            'value' 		=> 'Description for services',
            'description' 	=> 'options.market_products.description',
        ],
        [
            'code'          => 'market_proposals_description_uk',
            'value' 		=> 'Опис для cпільних пропозицій',
            'description' 	=> 'options.market_kits.description',
        ],
        [
            'code'          => 'market_proposals_description_en',
            'value' 		=> 'Description for proposals',
            'description' 	=> 'options.market_kits.description',
        ],
        [
            'code'          => 'footer_description_main_uk',
            'value' 		=> "<div>Український проект бізнес-розвитку плодоовочівництва (UHBDP) фінансується Міністерством міжнародних справ Канади, співфінансується та реалізується Менонітською Асоціацією Економічного Розвитку (MEDA).</div><span class='clear'></span><div>Використання матеріалів з сайту можливе без попереднього узгодження, але обов'язково з посиланням на https://uhbdp.org</div><span class='clear'></span><br/>",
            'description' 	=> 'options.footer_main.description',
        ],
        [
            'code'          => 'footer_description_main_en',
            'value' 		=> "<div>The Ukrainian Fruit and Vegetable Business Development Project (UHBDP) is funded by the Ministry of International Affairs of Canada, co-financed and implemented by the Mennonite Association for Economic Development (MEDA).</div><span class='clear'></span><div>Use of information from the site is possible without prior agreement, but always with a link to https://uhbdp.org</div><span class='clear'></span><br/>",
            'description' 	=> 'options.footer_main.description',
        ],
        [
            'code'          => 'footer_description_tel_uk',
            'value' 		=> '<p class="call-dropdown__text">Дзвінки безкоштовні з усіх номерів стаціонарних і мобільних операторів зв’язку України.</p><p class="call-dropdown__text"><strong>Пн-Пт, 08:00-17:00 </strong></p>',
            'description' 	=> 'options.footer_tel.description',
        ],
        [
            'code'          => 'footer_description_tel_en',
            'value' 		=> '<p class="call-dropdown__text">Calls are free from all numbers of fixed and mobile communication operators of Ukraine</p><p class="call-dropdown__text"><strong>Mon-Fri, 08:00-17:00 </strong></p>',
            'description' 	=> 'options.footer_tel.description',
        ],
    ];

    protected static $defaultName = 'app:create-index-page-edits';
    protected static $defaultDescription = 'Create option for admin';

    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existOption = $this->checkExistsOptions();

        foreach (self::OPTION_STRUCTURE as $option) {
            extract($option);

            if (isset($existOption[$code])){
                $options = $existOption[$code];
                $options->setDescription($this->translator->trans($description));
            } else {

                $options = new Options();

                $options->setCode($code);
                $options->setValue($value);
                $options->setDescription($this->translator->trans($description));
            }
            $this->entityManager->persist($options);
            $this->entityManager->flush();
        }
        $io->success('Counter options has been created!');

        return Command::SUCCESS;
    }

    private function checkExistsOptions():array
    {
        $arr = [];
        /**
         * @var Options[] $options
         */
        $options = $this->entityManager->getRepository(Options::class)->findBy([
            'code' => [
                'index_page_counter_webinar_value',
                'index_page_counter_course_value',
                'index_page_counter_commodity_value',
                'index_page_counter_buyer_value',
                'index_page_counter_webinar_description_uk',
                'index_page_counter_webinar_description_en',
                'index_page_counter_course_description_uk',
                'index_page_counter_course_description_en',
                'index_page_counter_commodity_description_uk',
                'index_page_counter_commodity_description_en',
                'index_page_counter_buyer_description_uk',
                'index_page_counter_buyer_description_en',
                'market_goods_description_uk',
                'market_goods_description_en',
                'market_services_description_uk',
                'market_services_description_en',
                'market_proposals_description_uk',
                'market_proposals_description_en',
                'footer_description_main_uk',
                'footer_description_main_en',
                'footer_description_tel_uk',
                'footer_description_tel_en',
            ]
        ]);

        foreach ($options as $option) {
            $arr[$option->getCode()] = $option;
        }

     return $arr;
    }
}
