<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210713062832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_part ADD expert_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_part ADD CONSTRAINT FK_81ADADC0C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id)');
        $this->addSql('CREATE INDEX IDX_81ADADC0C5568CE4 ON course_part (expert_id)');
        $this->addSql('ALTER TABLE lesson_module DROP FOREIGN KEY FK_7FB2D67FC5568CE4');
        $this->addSql('DROP INDEX IDX_7FB2D67FC5568CE4 ON lesson_module');
        $this->addSql('ALTER TABLE lesson_module DROP expert_id');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62024670C11');
        $this->addSql('DROP INDEX IDX_140AB62024670C11 ON page');
        $this->addSql('ALTER TABLE page DROP type_page_id, DROP image_name');
        $this->addSql('ALTER TABLE page_translation DROP short');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_part DROP FOREIGN KEY FK_81ADADC0C5568CE4');
        $this->addSql('DROP INDEX IDX_81ADADC0C5568CE4 ON course_part');
        $this->addSql('ALTER TABLE course_part DROP expert_id');
        $this->addSql('ALTER TABLE lesson_module ADD expert_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_module ADD CONSTRAINT FK_7FB2D67FC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7FB2D67FC5568CE4 ON lesson_module (expert_id)');
        $this->addSql('ALTER TABLE page ADD type_page_id INT DEFAULT NULL, ADD image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62024670C11 FOREIGN KEY (type_page_id) REFERENCES type_page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_140AB62024670C11 ON page (type_page_id)');
        $this->addSql('ALTER TABLE page_translation ADD short LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
