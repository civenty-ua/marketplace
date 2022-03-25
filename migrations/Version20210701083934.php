<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701083934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, site VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, cell_phone VARCHAR(255) DEFAULT NULL, INDEX IDX_4C62E63898260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, address VARCHAR(400) DEFAULT NULL, head VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, fullname VARCHAR(400) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_DAC5FAD12C2AC5D3 (translatable_id), UNIQUE INDEX contact_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63898260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE contact_translation ADD CONSTRAINT FK_DAC5FAD12C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES contact (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_translation DROP FOREIGN KEY FK_DAC5FAD12C2AC5D3');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contact_translation');
    }
}
