<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211101125611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_commodity_user_display_phone (commodity_id INT NOT NULL, phone_id INT NOT NULL, INDEX IDX_76308B95B4ACC212 (commodity_id), INDEX IDX_76308B953B7323CB (phone_id), PRIMARY KEY(commodity_id, phone_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_commodity_user_display_phone ADD CONSTRAINT FK_76308B95B4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_commodity_user_display_phone ADD CONSTRAINT FK_76308B953B7323CB FOREIGN KEY (phone_id) REFERENCES market_phone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_commodity ADD user_display_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE market_commodity_product_certificate RENAME INDEX idx_910fc1d1d4e9e2 TO IDX_A9DA46591D4E9E2');
        $this->addSql('ALTER TABLE market_commodity_product_certificate RENAME INDEX idx_910fc1df2a7e0a4 TO IDX_A9DA4659F2A7E0A4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE market_commodity_user_display_phone');
        $this->addSql('ALTER TABLE market_commodity DROP user_display_name');
        $this->addSql('ALTER TABLE market_commodity_product_certificate RENAME INDEX idx_a9da46591d4e9e2 TO IDX_910FC1D1D4E9E2');
        $this->addSql('ALTER TABLE market_commodity_product_certificate RENAME INDEX idx_a9da4659f2a7e0a4 TO IDX_910FC1DF2A7E0A4');
    }
}
