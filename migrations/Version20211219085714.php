<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211219085714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private function getSeoOptions()
    {
        $languages = ['uk', 'en'];

        $pages = [
            'home' => [
                'for' => 'головної сторінки',
                't' => 'Агровікі - перша платформа аграрних знань',
                'd' => 'Отримуйте консультації з агрономії та бізнесу від справжніх професіоналів України! Детальніше на сайті | АгроВікі | ☎ 0 800 500 184',
                'k' => 'агробізнес, агровікі, База аграрних знань'
            ],
            'news' => [
                'for' => 'сторінки «Новини»',
                't' => 'Новини | uhbdp.org',
                'd' => 'Новини | АгроВікі | Аграрії України ✓База аграрних знань ✓Бізнес розвиток плодоовочівництва ✓Бізнес інструменти ',
                'k' => ''
            ],
            'partners' => [
                'for' => 'сторінки «Партнери»',
                't' => 'Партнери | АгроВікі',
                'd' => 'Партнери | АгроВікі| Аграрії України ✓База аграрних знань ✓Бізнес розвиток плодоовочівництва ✓Бізнес інструменти',
                'k' => ''
            ],
            'experts' => [
                'for' => 'сторінки «Експерти»',
                't' => 'Експерти | uhbdp.org',
                'd' => 'Експерти | АгроВікі | Аграрії України ✓База аграрних знань ✓Бізнес розвиток плодоовочівництва ✓Бізнес інструменти',
                'k' => ''
            ],
            'contacts' => [
                'for' => 'сторінки «Контакти»',
                't' => 'Контакти | АгроВікі',
                'd' => 'Контакти | АгроВікі | Аграрії України ✓База аграрних знань ✓Бізнес розвиток плодоовочівництва ✓Бізнес інструменти',
                'k' => ''
            ],
            'success_stories' => [
                'for' => 'сторінки «Історії успіху»',
                't' => 'Історії успіху | АгроВікі',
                'd' => 'Історії успіху | АгроВікі | Аграрії України ✓База аграрних знань ✓Бізнес розвиток плодоовочівництва ✓Бізнес інструменти',
                'k' => ''
            ],
            'courses_and_webinars_course' => [
                'for' => 'сторінки «Навчання -> Курси»',
                't' => 'Курси для агробізнесу | АгроВікі',
                'd' => 'Курси та семінари для агробізнесу від АгроВікі для системного навчання для керівників, ТОП-менеджерів | АгроВікі | ☎ 0 800 500 184',
                'k' => ''
            ],
            'courses_and_webinars_webinar' => [
                'for' => 'сторінки «Навчання -> Вебінари»',
                't' => 'Вебінари - Агробізнес | АгроВікі',
                'd' => 'Курси та семінари для агробізнесу від АгроВікі для системного навчання для керівників, ТОП-менеджерів | АгроВікі | ☎ 0 800 500 184',
                'k' => ''
            ],
            'courses_and_webinars_article' => [
                'for' => 'сторінки «Навчання -> Статті»',
                't' => 'Статті про агробізнес | АгроВікі ',
                'd' => '',
                'k' => ''
            ],
            'courses_and_webinars_occurrence' => [
                'for' => 'сторінки «Навчання -> Заходи»',
                't' => 'Заходи  | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'courses_and_webinars_other' => [
                'for' => 'сторінки «Навчання -> Інше»',
                't' => 'Інше | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'calendar' => [
                'for' => 'сторінки «Календар»',
                't' => 'Календар аграрних заходів',
                'd' => 'Календар аграрних заходів ✓Календар подій ✓ Калькулятор для бджоляра ✓ Аграрна інформація. Детальніше на нашому сайті uhbdp.org',
                'k' => ''
            ],
            'eco_articles' => [
                'for' => 'сторінки «Еко-статті»',
                't' => 'ᐉ "Еко-статті" в Україні | АгроВікі',
                'd' => '"Еко-статті" | База аграрних знань ✅ Бізнес розвиток плодоовочівництва ★ Інноваційні технології ★ Детальна информація ☎ 0 800 500 184',
                'k' => ''
            ],
            'course' => [
                'tpl' => '{title} - назва курсу, {category} - категорія',
                'for' => 'сторінки «Курсу»',
                't' => 'Курс {title}',
                'd' => '',
                'k' => ''
            ],
            'webinar' => [
                'tpl' => '{title} - назва вебінару, {category} - категорія',
                'for' => 'сторінки «Вебінар»',
                't' => 'Вебінар {title}',
                'd' => '',
                'k' => ''
            ],
            'article' => [
                'tpl' => '{title} - назва статті, {category} - категорія',
                'for' => 'сторінки «Стаття»',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'category' => [
                'tpl' => '{title} - назва категорія',
                'for' => 'сторінки «Категорія»',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'page' => [
                'tpl' => '{title} - назва сторінки',
                'for' => 'сторінки',
                't' => 'ᐉ "{title}" | АгроВікі',
                'd' => '',
                'k' => ''
            ],
            'success_story' => [
                'tpl' => '{title} - назва Історії успіху',
                'for' => 'сторінки «Історія успіху»',
                't' => '{title} | АгроВікі',
                'd' => '',
                'k' => ''
            ],
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
        $this->addSql('ALTER TABLE article_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE course_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE crop_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE expert_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback_form_question_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback_form_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE news_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE occurrence_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE other_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE partner_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE video_item_translation ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE webinar_translation ADD meta_title VARCHAR(255) DEFAULT NULL');

        foreach ($this->getSeoOptions() as $code => $seoOption) {
            $this->addSql("INSERT INTO options (code, value, description, type) VALUES ('$code', '$seoOption[0]', '$seoOption[1]', 'seo')");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_translation DROP meta_title');
        $this->addSql('ALTER TABLE category_translation DROP meta_title');
        $this->addSql('ALTER TABLE course_translation DROP meta_title');
        $this->addSql('ALTER TABLE crop_translation DROP meta_title');
        $this->addSql('ALTER TABLE expert_translation DROP meta_title');
        $this->addSql('ALTER TABLE feedback_form_question_translation DROP meta_title');
        $this->addSql('ALTER TABLE feedback_form_translation DROP meta_title');
        $this->addSql('ALTER TABLE lesson_translation DROP meta_title');
        $this->addSql('ALTER TABLE news_translation DROP meta_title');
        $this->addSql('ALTER TABLE occurrence_translation DROP meta_title');
        $this->addSql('ALTER TABLE other_translation DROP meta_title');
        $this->addSql('ALTER TABLE page_translation DROP meta_title');
        $this->addSql('ALTER TABLE partner_translation DROP meta_title');
        $this->addSql('ALTER TABLE video_item_translation DROP meta_title');
        $this->addSql('ALTER TABLE webinar_translation DROP meta_title');

        foreach ($this->getSeoOptions() as $code => $seoOption) {
            $this->addSql("DELETE FROM options where code = '$code'");
        }
    }
}
