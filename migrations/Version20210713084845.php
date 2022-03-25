<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210713084845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP TABLE IF EXISTS dead_url');
        $this->addSql('CREATE TABLE dead_url (id INT AUTO_INCREMENT NOT NULL, dead_request VARCHAR(2000) DEFAULT NULL, redirect_to VARCHAR(1000) DEFAULT NULL, is_active TINYINT(1) DEFAULT NULL,attempt_amount INTEGER DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dead_url ADD check_sum INT DEFAULT NULL');
        $this->addSql('CREATE INDEX url_checksum_idx ON dead_url (check_sum)');
        $this->addSql('ALTER TABLE expert DROP social_networks');
        $this->addSql('ALTER TABLE page ADD type_page_id INT DEFAULT NULL, ADD image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62024670C11 FOREIGN KEY (type_page_id) REFERENCES type_page (id)');
        $this->addSql('CREATE INDEX IDX_140AB62024670C11 ON page (type_page_id)');
        $this->addSql('ALTER TABLE page_translation ADD short LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dead_url MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE dead_url DROP INDEX primary, ADD INDEX id (id)');
        $this->addSql('ALTER TABLE expert ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62024670C11');
        $this->addSql('DROP INDEX IDX_140AB62024670C11 ON page');
        $this->addSql('ALTER TABLE page DROP type_page_id, DROP image_name');
        $this->addSql('ALTER TABLE page_translation DROP short');
    }
}
