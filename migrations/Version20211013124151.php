<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013124151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD course_banner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1EAEF889F FOREIGN KEY (course_banner_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1EAEF889F ON category (course_banner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1EAEF889F');
        $this->addSql('DROP INDEX IDX_64C19C1EAEF889F ON category');
        $this->addSql('ALTER TABLE category DROP course_banner_id');
    }
}
