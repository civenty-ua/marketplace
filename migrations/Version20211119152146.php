<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211119152146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_commodity_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, commodity_id INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, notification_sent TINYINT(1) DEFAULT \'0\' NOT NULL, event_type TINYINT(1) DEFAULT NULL , INDEX IDX_5932D0EDA76ED395 (user_id), INDEX IDX_5932D0EDB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_commodity_notification ADD CONSTRAINT FK_5932D0EDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_commodity_notification ADD CONSTRAINT FK_5932D0EDB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_commodity_notification');
    }
}
