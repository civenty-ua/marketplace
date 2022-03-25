<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622144651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lesson_module_lesson');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE lesson ADD lesson_module_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3CBE15A1B FOREIGN KEY (lesson_module_id) REFERENCES lesson_module (id)');
        $this->addSql('CREATE INDEX IDX_F87474F3CBE15A1B ON lesson (lesson_module_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lesson_module_lesson (lesson_module_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_865B273ECBE15A1B (lesson_module_id), INDEX IDX_865B273ECDF80196 (lesson_id), PRIMARY KEY(lesson_module_id, lesson_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE lesson_module_lesson ADD CONSTRAINT FK_865B273ECBE15A1B FOREIGN KEY (lesson_module_id) REFERENCES lesson_module (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_module_lesson ADD CONSTRAINT FK_865B273ECDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3CBE15A1B');
        $this->addSql('DROP INDEX IDX_F87474F3CBE15A1B ON lesson');
        $this->addSql('ALTER TABLE lesson DROP lesson_module_id');
    }
}
