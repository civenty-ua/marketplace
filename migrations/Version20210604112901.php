<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210604112901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crop (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) NOT NULL, alias VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crop_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_A3546FB82C2AC5D3 (translatable_id), UNIQUE INDEX crop_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crop_translation ADD CONSTRAINT FK_A3546FB82C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES crop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE item ADD crop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E888579EE FOREIGN KEY (crop_id) REFERENCES crop (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E888579EE ON item (crop_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crop_translation DROP FOREIGN KEY FK_A3546FB82C2AC5D3');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E888579EE');
        $this->addSql('DROP TABLE crop');
        $this->addSql('DROP TABLE crop_translation');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX IDX_1F1B251E888579EE ON item');
        $this->addSql('ALTER TABLE item DROP crop_id');
    }
}
