<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211115121111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_notification_price_offer ADD commodity_id INT DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD phone VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE market_notification_price_offer ADD CONSTRAINT FK_FDEECC8DB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('CREATE INDEX IDX_FDEECC8DB4ACC212 ON market_notification_price_offer (commodity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_notification_price_offer DROP FOREIGN KEY FK_FDEECC8DB4ACC212');
        $this->addSql('DROP INDEX IDX_FDEECC8DB4ACC212 ON market_notification_price_offer');
        $this->addSql('ALTER TABLE market_notification_price_offer DROP commodity_id, DROP name, DROP phone, DROP email, DROP price');
    }
}
