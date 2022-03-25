<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210608070412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_tags (item_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_A78CD0DD126F525E (item_id), INDEX IDX_A78CD0DD8D7B4FB4 (tags_id), PRIMARY KEY(item_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_partner (item_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_3A761842126F525E (item_id), INDEX IDX_3A7618429393F8FE (partner_id), PRIMARY KEY(item_id, partner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_expert (item_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_C9E2E68D126F525E (item_id), INDEX IDX_C9E2E68DC5568CE4 (expert_id), PRIMARY KEY(item_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_tags ADD CONSTRAINT FK_A78CD0DD126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_tags ADD CONSTRAINT FK_A78CD0DD8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_partner ADD CONSTRAINT FK_3A761842126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_partner ADD CONSTRAINT FK_3A7618429393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_expert ADD CONSTRAINT FK_C9E2E68D126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_expert ADD CONSTRAINT FK_C9E2E68DC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE article_expert');
        $this->addSql('DROP TABLE article_partner');
        $this->addSql('DROP TABLE article_tags');
        $this->addSql('DROP TABLE course_expert');
        $this->addSql('DROP TABLE course_partner');
        $this->addSql('DROP TABLE course_tags');
        $this->addSql('DROP TABLE other_tags');
        $this->addSql('DROP TABLE webinar_expert');
        $this->addSql('DROP TABLE webinar_partner');
        $this->addSql('DROP TABLE webinar_tags');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('DROP INDEX IDX_23A0E6612469DE2 ON article');
        $this->addSql('ALTER TABLE article DROP category_id');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB912469DE2');
        $this->addSql('DROP INDEX IDX_169E6FB912469DE2 ON course');
        $this->addSql('ALTER TABLE course DROP category_id');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE item ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E12469DE2 ON item (category_id)');
        $this->addSql('ALTER TABLE other DROP FOREIGN KEY FK_D958352012469DE2');
        $this->addSql('DROP INDEX IDX_D958352012469DE2 ON other');
        $this->addSql('ALTER TABLE other DROP category_id');
        $this->addSql('ALTER TABLE webinar DROP FOREIGN KEY FK_C9E29F4A12469DE2');
        $this->addSql('DROP INDEX IDX_C9E29F4A12469DE2 ON webinar');
        $this->addSql('ALTER TABLE webinar DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_expert (article_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_25DC04C27294869C (article_id), INDEX IDX_25DC04C2C5568CE4 (expert_id), PRIMARY KEY(article_id, expert_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE article_partner (article_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_DCF97AA17294869C (article_id), INDEX IDX_DCF97AA19393F8FE (partner_id), PRIMARY KEY(article_id, partner_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE article_tags (article_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_DFFE13277294869C (article_id), INDEX IDX_DFFE13278D7B4FB4 (tags_id), PRIMARY KEY(article_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE course_expert (course_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_E123520F591CC992 (course_id), INDEX IDX_E123520FC5568CE4 (expert_id), PRIMARY KEY(course_id, expert_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE course_partner (course_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_39E83BFA591CC992 (course_id), INDEX IDX_39E83BFA9393F8FE (partner_id), PRIMARY KEY(course_id, partner_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE course_tags (course_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_A71E4920591CC992 (course_id), INDEX IDX_A71E49208D7B4FB4 (tags_id), PRIMARY KEY(course_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE other_tags (other_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_6F8C2F48D7B4FB4 (tags_id), INDEX IDX_6F8C2F4998D9879 (other_id), PRIMARY KEY(other_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE webinar_expert (webinar_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_E332CF53A391D86E (webinar_id), INDEX IDX_E332CF53C5568CE4 (expert_id), PRIMARY KEY(webinar_id, expert_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE webinar_partner (webinar_id INT NOT NULL, partner_id INT NOT NULL, INDEX IDX_5B3737B89393F8FE (partner_id), INDEX IDX_5B3737B8A391D86E (webinar_id), PRIMARY KEY(webinar_id, partner_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE webinar_tags (webinar_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_75A73DEE8D7B4FB4 (tags_id), INDEX IDX_75A73DEEA391D86E (webinar_id), PRIMARY KEY(webinar_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE article_expert ADD CONSTRAINT FK_25DC04C27294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_expert ADD CONSTRAINT FK_25DC04C2C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_partner ADD CONSTRAINT FK_DCF97AA17294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_partner ADD CONSTRAINT FK_DCF97AA19393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE13277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE13278D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_expert ADD CONSTRAINT FK_E123520F591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_expert ADD CONSTRAINT FK_E123520FC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_partner ADD CONSTRAINT FK_39E83BFA591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_partner ADD CONSTRAINT FK_39E83BFA9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_tags ADD CONSTRAINT FK_A71E4920591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_tags ADD CONSTRAINT FK_A71E49208D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE other_tags ADD CONSTRAINT FK_6F8C2F48D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE other_tags ADD CONSTRAINT FK_6F8C2F4998D9879 FOREIGN KEY (other_id) REFERENCES other (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_expert ADD CONSTRAINT FK_E332CF53A391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_expert ADD CONSTRAINT FK_E332CF53C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_partner ADD CONSTRAINT FK_5B3737B89393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_partner ADD CONSTRAINT FK_5B3737B8A391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_tags ADD CONSTRAINT FK_75A73DEE8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webinar_tags ADD CONSTRAINT FK_75A73DEEA391D86E FOREIGN KEY (webinar_id) REFERENCES webinar (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE item_tags');
        $this->addSql('DROP TABLE item_partner');
        $this->addSql('DROP TABLE item_expert');
        $this->addSql('ALTER TABLE article ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_23A0E6612469DE2 ON article (category_id)');
        $this->addSql('ALTER TABLE course ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_169E6FB912469DE2 ON course (category_id)');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E12469DE2');
        $this->addSql('DROP INDEX IDX_1F1B251E12469DE2 ON item');
        $this->addSql('ALTER TABLE item DROP category_id');
        $this->addSql('ALTER TABLE other ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE other ADD CONSTRAINT FK_D958352012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D958352012469DE2 ON other (category_id)');
        $this->addSql('ALTER TABLE webinar ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE webinar ADD CONSTRAINT FK_C9E29F4A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C9E29F4A12469DE2 ON webinar (category_id)');
    }
}
