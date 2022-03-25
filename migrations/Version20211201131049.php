<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211201131049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE market_kit_agreement');
        $this->addSql('ALTER TABLE market_notification_kit_agreement_notification ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_kit_agreement (id INT AUTO_INCREMENT NOT NULL, kit_id INT NOT NULL, commodity_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_CD3DAE93A8E60EF (kit_id), INDEX IDX_CD3DAE9B4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_kit_agreement ADD CONSTRAINT FK_CD3DAE93A8E60EF FOREIGN KEY (kit_id) REFERENCES market_commodity_kit (id)');
        $this->addSql('ALTER TABLE market_kit_agreement ADD CONSTRAINT FK_CD3DAE9B4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE market_notification_kit_agreement_notification DROP status');
    }
}
