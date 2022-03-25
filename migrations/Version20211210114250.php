<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211210114250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lesson_sort (id INT AUTO_INCREMENT NOT NULL, lesson_id INT NOT NULL, lesson_module_id INT NOT NULL, sort INT NOT NULL, INDEX IDX_68553446CDF80196 (lesson_id), INDEX IDX_68553446CBE15A1B (lesson_module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lesson_sort ADD CONSTRAINT FK_68553446CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE lesson_sort ADD CONSTRAINT FK_68553446CBE15A1B FOREIGN KEY (lesson_module_id) REFERENCES lesson_module (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lesson_sort');
    }
}
