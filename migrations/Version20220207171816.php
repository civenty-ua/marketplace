<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220207171816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private function getSeoOptions()
    {
        $languages = ['uk', 'en'];

        $pages = [
            'partner' => [
                'tpl' => '{name} - партнер',
                'for' => 'сторінки партнера',
                't' => '{name} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'expert' => [
                'tpl' => '{name} - експерт',
                'for' => 'сторінки експерта',
                't' => '{name} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_product' => [
                'tpl' => '{title} - назва товару',
                'for' => 'сторінки товару (Торгівельний майданчик)',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_service' => [
                'tpl' => '{title} - назва сервісу',
                'for' => 'сторінки сервісу (Торгівельний майданчик)',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_kit' => [
                'tpl' => '{title} - назва пропозиції',
                'for' => 'сторінки пропозиції (Торгівельний майданчик)',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_category' => [
                'tpl' => '{title} - назва категорії',
                'for' => 'сторінки категорії (Торгівельний майданчик)',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_products' => [
                'for' => 'сторінки «Торгівельний майданчик -> Товари»',
                't' => 'Товари | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_services' => [
                'for' => 'сторінки «Торгівельний майданчик -> Послуги»',
                't' => 'Послуги | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'marketplace_kits' => [
                'for' => 'сторінки «Торгівельний майданчик -> Пропозиції»',
                't' => 'Пропозиції | АгроВікі',
                'd' => '',
                'k' => ''
            ]
        ];

        $seoOptions = [];

        foreach ($pages as $pageCode => $data) {
            $title = isset($data['tpl']) ? 'Шаблон метатегу' : 'Метатег';
            $hint = isset($data['tpl']) ? ' [можна використовувати наступні змінні: ' . $data['tpl'] . '] ' : '';

            foreach ($languages as $language) {
                $seoOptions['meta_title_' . $pageCode . '_' . $language] = [
                    $data['t'],
                    $title . ' title для ' . $data['for'] . $hint . ' (' . $language . ')'
                ];
            }

            foreach ($languages as $language) {
                $seoOptions['meta_description_' . $pageCode . '_' . $language] = [
                    $data['d'],
                    $title . ' description для ' . $data['for'] . $hint .  ' (' . $language . ')'
                ];
            }

            foreach ($languages as $language) {
                $seoOptions['meta_keywords_' . $pageCode . '_' . $language] = [
                    $data['k'],
                    $title . ' keywords для ' . $data['for'] . $hint .  ' (' . $language . ')'
                ];
            }
        }

        return $seoOptions;
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_category ADD meta_title VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE market_commodity ADD meta_title VARCHAR(255) DEFAULT NULL, ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL');

        foreach ($this->getSeoOptions() as $code => $seoOption) {
            $this->addSql("INSERT INTO options (code, value, description, type) VALUES ('$code', '$seoOption[0]', '$seoOption[1]', 'seo')");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_category DROP meta_title, DROP meta_keywords, DROP meta_description');
        $this->addSql('ALTER TABLE market_commodity DROP meta_title, DROP meta_keywords, DROP meta_description');

        foreach ($this->getSeoOptions() as $code => $seoOption) {
            $this->addSql("DELETE FROM options where code = '$code'");
        }
    }
}
