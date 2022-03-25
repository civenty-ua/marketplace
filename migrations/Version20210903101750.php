<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210903101750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity DROP FOREIGN KEY FK_CBDFD4E312469DE2');
        $this->addSql('DROP INDEX IDX_CBDFD4E312469DE2 ON market_commodity');
        $this->addSql('ALTER TABLE market_commodity DROP category_id');
        $this->addSql('ALTER TABLE market_commodity_product ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_commodity_product ADD CONSTRAINT FK_887C973412469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id)');
        $this->addSql('CREATE INDEX IDX_887C973412469DE2 ON market_commodity_product (category_id)');
        $this->addSql('ALTER TABLE market_commodity_service ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_commodity_service ADD CONSTRAINT FK_BAAB094B12469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id)');
        $this->addSql('CREATE INDEX IDX_BAAB094B12469DE2 ON market_commodity_service (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_commodity ADD CONSTRAINT FK_CBDFD4E312469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CBDFD4E312469DE2 ON market_commodity (category_id)');
        $this->addSql('ALTER TABLE market_commodity_product DROP FOREIGN KEY FK_887C973412469DE2');
        $this->addSql('DROP INDEX IDX_887C973412469DE2 ON market_commodity_product');
        $this->addSql('ALTER TABLE market_commodity_product DROP category_id');
        $this->addSql('ALTER TABLE market_commodity_service DROP FOREIGN KEY FK_BAAB094B12469DE2');
        $this->addSql('DROP INDEX IDX_BAAB094B12469DE2 ON market_commodity_service');
        $this->addSql('ALTER TABLE market_commodity_service DROP category_id');
    }
}
