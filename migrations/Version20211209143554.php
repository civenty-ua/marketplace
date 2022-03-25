<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211209143554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('CREATE TABLE course_part_sort (id INT AUTO_INCREMENT NOT NULL, course_part_id INT NOT NULL, course_id INT NOT NULL, sort INT NOT NULL, INDEX IDX_A92DCC0344204E00 (course_part_id), INDEX IDX_A92DCC03591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course_part_sort ADD CONSTRAINT FK_A92DCC0344204E00 FOREIGN KEY (course_part_id) REFERENCES course_part (id)');
        $this->addSql('ALTER TABLE course_part_sort ADD CONSTRAINT FK_A92DCC03591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
          }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE course_part_sort');

    }
}
