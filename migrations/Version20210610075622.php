<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610075622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course_part (id INT AUTO_INCREMENT NOT NULL, course_part_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_81ADADC044204E00 (course_part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson_module (id INT NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson_module_lesson (lesson_module_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_865B273ECBE15A1B (lesson_module_id), INDEX IDX_865B273ECDF80196 (lesson_id), PRIMARY KEY(lesson_module_id, lesson_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson_module_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_FEF99CEC2C2AC5D3 (translatable_id), UNIQUE INDEX lesson_module_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course_part ADD CONSTRAINT FK_81ADADC044204E00 FOREIGN KEY (course_part_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson_module ADD CONSTRAINT FK_7FB2D67FBF396750 FOREIGN KEY (id) REFERENCES course_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_module_lesson ADD CONSTRAINT FK_865B273ECBE15A1B FOREIGN KEY (lesson_module_id) REFERENCES lesson_module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_module_lesson ADD CONSTRAINT FK_865B273ECDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_module_translation ADD CONSTRAINT FK_FEF99CEC2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES lesson_module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('DROP INDEX IDX_F87474F3591CC992 ON lesson');
        $this->addSql('ALTER TABLE lesson DROP course_id');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3BF396750 FOREIGN KEY (id) REFERENCES course_part (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3BF396750');
        $this->addSql('ALTER TABLE lesson_module DROP FOREIGN KEY FK_7FB2D67FBF396750');
        $this->addSql('ALTER TABLE lesson_module_lesson DROP FOREIGN KEY FK_865B273ECBE15A1B');
        $this->addSql('ALTER TABLE lesson_module_translation DROP FOREIGN KEY FK_FEF99CEC2C2AC5D3');
        $this->addSql('DROP TABLE course_part');
        $this->addSql('DROP TABLE lesson_module');
        $this->addSql('DROP TABLE lesson_module_lesson');
        $this->addSql('DROP TABLE lesson_module_translation');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE lesson ADD course_id INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F87474F3591CC992 ON lesson (course_id)');
    }
}
