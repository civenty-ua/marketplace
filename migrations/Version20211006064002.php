<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006064002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE market_user_commodity_favorite TO market_commodity_favorite');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE market_commodity_favorite TO market_user_commodity_favorite');
    }
}
