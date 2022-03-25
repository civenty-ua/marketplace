<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913110727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity_product ADD region_id INT, ADD district_id INT, ADD locality_id INT');
        $this->addSql('ALTER TABLE market_commodity_product ADD CONSTRAINT FK_887C973498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE market_commodity_product ADD CONSTRAINT FK_887C9734B08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
        $this->addSql('ALTER TABLE market_commodity_product ADD CONSTRAINT FK_887C973488823A92 FOREIGN KEY (locality_id) REFERENCES locality (id)');
        $this->addSql('CREATE INDEX IDX_887C973498260155 ON market_commodity_product (region_id)');
        $this->addSql('CREATE INDEX IDX_887C9734B08FA272 ON market_commodity_product (district_id)');
        $this->addSql('CREATE INDEX IDX_887C973488823A92 ON market_commodity_product (locality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity_product DROP FOREIGN KEY FK_887C973498260155');
        $this->addSql('ALTER TABLE market_commodity_product DROP FOREIGN KEY FK_887C9734B08FA272');
        $this->addSql('ALTER TABLE market_commodity_product DROP FOREIGN KEY FK_887C973488823A92');
        $this->addSql('DROP INDEX IDX_887C973498260155 ON market_commodity_product');
        $this->addSql('DROP INDEX IDX_887C9734B08FA272 ON market_commodity_product');
        $this->addSql('DROP INDEX IDX_887C973488823A92 ON market_commodity_product');
        $this->addSql('ALTER TABLE market_commodity_product DROP region_id, DROP district_id, DROP locality_id');
    }
}
