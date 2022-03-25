<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020115454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_user_certificate (id INT AUTO_INCREMENT NOT NULL, user_property_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, is_ecology TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, file_size INT DEFAULT NULL, original_name VARCHAR(255) DEFAULT NULL, INDEX IDX_15F03F8AFD89DA79 (user_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_user_certificate ADD CONSTRAINT FK_15F03F8AFD89DA79 FOREIGN KEY (user_property_id) REFERENCES market_user_property (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE market_user_certificate');
    }
}
