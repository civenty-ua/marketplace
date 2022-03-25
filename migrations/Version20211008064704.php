<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211008064704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE view_in_menu view_in_menu TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE market_commodity_favorite RENAME INDEX idx_bd462b2fa76ed395 TO IDX_E14D93AAA76ED395');
        $this->addSql('ALTER TABLE market_commodity_favorite RENAME INDEX idx_bd462b2fb4acc212 TO IDX_E14D93AAB4ACC212');
        $this->addSql('ALTER TABLE market_notification_bit_offer CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE view_in_menu view_in_menu TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE market_commodity_favorite RENAME INDEX idx_e14d93aaa76ed395 TO IDX_BD462B2FA76ED395');
        $this->addSql('ALTER TABLE market_commodity_favorite RENAME INDEX idx_e14d93aab4acc212 TO IDX_BD462B2FB4ACC212');
        $this->addSql('ALTER TABLE market_notification_bit_offer CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
