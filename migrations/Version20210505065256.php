<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505065256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT NOT NULL, category_id INT DEFAULT NULL, type_page_id INT NOT NULL, image_name VARCHAR(255) NOT NULL, INDEX IDX_23A0E6612469DE2 (category_id), INDEX IDX_23A0E6624670C11 (type_page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_tags (article_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_DFFE13277294869C (article_id), INDEX IDX_DFFE13278D7B4FB4 (tags_id), PRIMARY KEY(article_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_partner (article_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_DCF97AA17294869C (article_id), INDEX IDX_DCF97AA19393F8FE (partner_id), PRIMARY KEY(article_id, partner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_expert (article_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_25DC04C27294869C (article_id), INDEX IDX_25DC04C2C5568CE4 (expert_id), PRIMARY KEY(article_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_published TINYINT(1) DEFAULT NULL, shorts LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_2EEA2F082C2AC5D3 (translatable_id), UNIQUE INDEX article_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_3F207042C2AC5D3 (translatable_id), UNIQUE INDEX category_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_tags (course_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_A71E4920591CC992 (course_id), INDEX IDX_A71E49208D7B4FB4 (tags_id), PRIMARY KEY(course_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_partner (course_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_39E83BFA591CC992 (course_id), INDEX IDX_39E83BFA9393F8FE (partner_id), PRIMARY KEY(course_id, partner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_expert (course_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_E123520F591CC992 (course_id), INDEX IDX_E123520FC5568CE4 (expert_id), PRIMARY KEY(course_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_C00775BB2C2AC5D3 (translatable_id), UNIQUE INDEX course_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, site VARCHAR(255) DEFAULT NULL, social_networks VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_A81CFF622C2AC5D3 (translatable_id), UNIQUE INDEX expert_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback_form (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback_form_question (id INT AUTO_INCREMENT NOT NULL, feedback_form_id INT NOT NULL, type ENUM(\'string\', \'number\'), required TINYINT(1) NOT NULL, sort INT NOT NULL, INDEX IDX_135012A1CEAFBBDE (feedback_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback_form_question_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, parameters JSON DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_D3448A102C2AC5D3 (translatable_id), UNIQUE INDEX feedback_form_question_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback_form_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_72A60832C2AC5D3 (translatable_id), UNIQUE INDEX feedback_form_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, feedback_form_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, registration_required TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_1F1B251ECEAFBBDE (feedback_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_F87474F3591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_45B0AE642C2AC5D3 (translatable_id), UNIQUE INDEX lesson_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, site VARCHAR(255) DEFAULT NULL, social_networks VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_FD1AF3212C2AC5D3 (translatable_id), UNIQUE INDEX partner_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_63062E9D2C2AC5D3 (translatable_id), UNIQUE INDEX tags_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_page (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_page_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_F44A9AAC2C2AC5D3 (translatable_id), UNIQUE INDEX type_page_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, gender INT DEFAULT NULL, date_of_birth DATE DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_feedback (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, form_id INT NOT NULL, rate INT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_32A4A058A76ED395 (user_id), INDEX IDX_32A4A058126F525E (item_id), INDEX IDX_32A4A0585FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_feedback_answer (id INT AUTO_INCREMENT NOT NULL, feedback_id INT NOT NULL, question_id INT NOT NULL, answer LONGTEXT DEFAULT NULL, INDEX IDX_F54D12E4D249A887 (feedback_id), INDEX IDX_F54D12E41E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_item (id INT AUTO_INCREMENT NOT NULL, lesson_id INT DEFAULT NULL, youtube_link VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_188C78C2CDF80196 (lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_item_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_F98B0B4E2C2AC5D3 (translatable_id), UNIQUE INDEX video_item_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webinar (id INT NOT NULL, video_item_id INT DEFAULT NULL, INDEX IDX_C9E29F4AF782BBF3 (video_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webinar_tags (webinar_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_75A73DEEA391D86E (webinar_id), INDEX IDX_75A73DEE8D7B4FB4 (tags_id), PRIMARY KEY(webinar_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webinar_partner (webinar_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_5B3737B8A391D86E (webinar_id), INDEX IDX_5B3737B89393F8FE (partner_id), PRIMARY KEY(webinar_id, partner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webinar_expert (webinar_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_E332CF53A391D86E (webinar_id), INDEX IDX_E332CF53C5568CE4 (expert_id), PRIMARY KEY(webinar_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webinar_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_5D1CC5CD2C2AC5D3 (translatable_id), UNIQUE INDEX webinar_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6624670C11 FOREIGN KEY (type_page_id) REFERENCES type_page (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66BF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE13277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE13278D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_partner ADD CONSTRAINT FK_DCF97AA17294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_partner ADD CONSTRAINT FK_DCF97AA19393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_expert ADD CONSTRAINT FK_25DC04C27294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_expert ADD CONSTRAINT FK_25DC04C2C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F082C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F207042C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9BF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_tags ADD CONSTRAINT FK_A71E4920591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_tags ADD CONSTRAINT FK_A71E49208D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_partner ADD CONSTRAINT FK_39E83BFA591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_partner ADD CONSTRAINT FK_39E83BFA9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_expert ADD CONSTRAINT FK_E123520F591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_expert ADD CONSTRAINT FK_E123520FC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_translation ADD CONSTRAINT FK_C00775BB2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expert_translation ADD CONSTRAINT FK_A81CFF622C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question ADD CONSTRAINT FK_135012A1CEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id)');
        $this->addSql('ALTER TABLE feedback_form_question_translation ADD CONSTRAINT FK_D3448A102C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES feedback_form_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_translation ADD CONSTRAINT FK_72A60832C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES feedback_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251ECEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson_translation ADD CONSTRAINT FK_45B0AE642C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partner_translation ADD CONSTRAINT FK_FD1AF3212C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tags_translation ADD CONSTRAINT FK_63062E9D2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_page_translation ADD CONSTRAINT FK_F44A9AAC2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES type_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A0585FF69B7D FOREIGN KEY (form_id) REFERENCES feedback_form (id)');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E4D249A887 FOREIGN KEY (feedback_id) REFERENCES user_feedback (id)');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E41E27F6BF FOREIGN KEY (question_id) REFERENCES feedback_form_question (id)');
        $this->addSql('ALTER TABLE video_item ADD CONSTRAINT FK_188C78C2CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE video_item_translation ADD CONSTRAINT FK_F98B0B4E2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES video_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar ADD CONSTRAINT FK_C9E29F4AF782BBF3 FOREIGN KEY (video_item_id) REFERENCES video_item (id)');
        $this->addSql('ALTER TABLE webinar ADD CONSTRAINT FK_C9E29F4ABF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_tags ADD CONSTRAINT FK_75A73DEEA391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_tags ADD CONSTRAINT FK_75A73DEE8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_partner ADD CONSTRAINT FK_5B3737B8A391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_partner ADD CONSTRAINT FK_5B3737B89393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_expert ADD CONSTRAINT FK_E332CF53A391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_expert ADD CONSTRAINT FK_E332CF53C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_translation ADD CONSTRAINT FK_5D1CC5CD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES webinar (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_tags DROP FOREIGN KEY FK_DFFE13277294869C');
        $this->addSql('ALTER TABLE article_partner DROP FOREIGN KEY FK_DCF97AA17294869C');
        $this->addSql('ALTER TABLE article_expert DROP FOREIGN KEY FK_25DC04C27294869C');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F082C2AC5D3');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F207042C2AC5D3');
        $this->addSql('ALTER TABLE course_tags DROP FOREIGN KEY FK_A71E4920591CC992');
        $this->addSql('ALTER TABLE course_partner DROP FOREIGN KEY FK_39E83BFA591CC992');
        $this->addSql('ALTER TABLE course_expert DROP FOREIGN KEY FK_E123520F591CC992');
        $this->addSql('ALTER TABLE course_translation DROP FOREIGN KEY FK_C00775BB2C2AC5D3');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE article_expert DROP FOREIGN KEY FK_25DC04C2C5568CE4');
        $this->addSql('ALTER TABLE course_expert DROP FOREIGN KEY FK_E123520FC5568CE4');
        $this->addSql('ALTER TABLE expert_translation DROP FOREIGN KEY FK_A81CFF622C2AC5D3');
        $this->addSql('ALTER TABLE webinar_expert DROP FOREIGN KEY FK_E332CF53C5568CE4');
        $this->addSql('ALTER TABLE feedback_form_question DROP FOREIGN KEY FK_135012A1CEAFBBDE');
        $this->addSql('ALTER TABLE feedback_form_translation DROP FOREIGN KEY FK_72A60832C2AC5D3');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251ECEAFBBDE');
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A0585FF69B7D');
        $this->addSql('ALTER TABLE feedback_form_question_translation DROP FOREIGN KEY FK_D3448A102C2AC5D3');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E41E27F6BF');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66BF396750');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9BF396750');
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A058126F525E');
        $this->addSql('ALTER TABLE webinar DROP FOREIGN KEY FK_C9E29F4ABF396750');
        $this->addSql('ALTER TABLE lesson_translation DROP FOREIGN KEY FK_45B0AE642C2AC5D3');
        $this->addSql('ALTER TABLE video_item DROP FOREIGN KEY FK_188C78C2CDF80196');
        $this->addSql('ALTER TABLE article_partner DROP FOREIGN KEY FK_DCF97AA19393F8FE');
        $this->addSql('ALTER TABLE course_partner DROP FOREIGN KEY FK_39E83BFA9393F8FE');
        $this->addSql('ALTER TABLE partner_translation DROP FOREIGN KEY FK_FD1AF3212C2AC5D3');
        $this->addSql('ALTER TABLE webinar_partner DROP FOREIGN KEY FK_5B3737B89393F8FE');
        $this->addSql('ALTER TABLE article_tags DROP FOREIGN KEY FK_DFFE13278D7B4FB4');
        $this->addSql('ALTER TABLE course_tags DROP FOREIGN KEY FK_A71E49208D7B4FB4');
        $this->addSql('ALTER TABLE tags_translation DROP FOREIGN KEY FK_63062E9D2C2AC5D3');
        $this->addSql('ALTER TABLE webinar_tags DROP FOREIGN KEY FK_75A73DEE8D7B4FB4');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6624670C11');
        $this->addSql('ALTER TABLE type_page_translation DROP FOREIGN KEY FK_F44A9AAC2C2AC5D3');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A058A76ED395');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E4D249A887');
        $this->addSql('ALTER TABLE video_item_translation DROP FOREIGN KEY FK_F98B0B4E2C2AC5D3');
        $this->addSql('ALTER TABLE webinar DROP FOREIGN KEY FK_C9E29F4AF782BBF3');
        $this->addSql('ALTER TABLE webinar_tags DROP FOREIGN KEY FK_75A73DEEA391D86E');
        $this->addSql('ALTER TABLE webinar_partner DROP FOREIGN KEY FK_5B3737B8A391D86E');
        $this->addSql('ALTER TABLE webinar_expert DROP FOREIGN KEY FK_E332CF53A391D86E');
        $this->addSql('ALTER TABLE webinar_translation DROP FOREIGN KEY FK_5D1CC5CD2C2AC5D3');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_tags');
        $this->addSql('DROP TABLE article_partner');
        $this->addSql('DROP TABLE article_expert');
        $this->addSql('DROP TABLE article_translation');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_translation');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE course_tags');
        $this->addSql('DROP TABLE course_partner');
        $this->addSql('DROP TABLE course_expert');
        $this->addSql('DROP TABLE course_translation');
        $this->addSql('DROP TABLE expert');
        $this->addSql('DROP TABLE expert_translation');
        $this->addSql('DROP TABLE feedback_form');
        $this->addSql('DROP TABLE feedback_form_question');
        $this->addSql('DROP TABLE feedback_form_question_translation');
        $this->addSql('DROP TABLE feedback_form_translation');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE lesson_translation');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE partner_translation');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE tags_translation');
        $this->addSql('DROP TABLE type_page');
        $this->addSql('DROP TABLE type_page_translation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_feedback');
        $this->addSql('DROP TABLE user_feedback_answer');
        $this->addSql('DROP TABLE video_item');
        $this->addSql('DROP TABLE video_item_translation');
        $this->addSql('DROP TABLE webinar');
        $this->addSql('DROP TABLE webinar_tags');
        $this->addSql('DROP TABLE webinar_partner');
        $this->addSql('DROP TABLE webinar_expert');
        $this->addSql('DROP TABLE webinar_translation');
    }
}
