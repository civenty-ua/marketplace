<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617115550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lesson_module_expert (lesson_module_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_3134C08FCBE15A1B (lesson_module_id), INDEX IDX_3134C08FC5568CE4 (expert_id), PRIMARY KEY(lesson_module_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lesson_module_expert ADD CONSTRAINT FK_3134C08FCBE15A1B FOREIGN KEY (lesson_module_id) REFERENCES lesson_module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_module_expert ADD CONSTRAINT FK_3134C08FC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE lesson_module ADD partner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_module ADD CONSTRAINT FK_7FB2D67F9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)');
        $this->addSql('CREATE INDEX IDX_7FB2D67F9393F8FE ON lesson_module (partner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lesson_module_expert');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE lesson_module DROP FOREIGN KEY FK_7FB2D67F9393F8FE');
        $this->addSql('DROP INDEX IDX_7FB2D67F9393F8FE ON lesson_module');
        $this->addSql('ALTER TABLE lesson_module DROP partner_id');
    }
}
