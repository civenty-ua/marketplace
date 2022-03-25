<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211103133414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_user_property ADD commodity_active_to_extended_by_days INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE market_user_property ADD allowed_amount_of_selling_commodities INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_user_property DROP commodity_active_to_extended_by_days');
        $this->addSql('ALTER TABLE market_user_property DROP allowed_amount_of_selling_commodities');
    }
}
