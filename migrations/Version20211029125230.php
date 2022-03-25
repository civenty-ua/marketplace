<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029125230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_notification_kit_agreement_notification (id INT NOT NULL, commodity_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, INDEX IDX_F8D1BC1CB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_notification_kit_agreement_notification ADD CONSTRAINT FK_F8D1BC1CB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity_kit (id)');
        $this->addSql('ALTER TABLE market_notification_kit_agreement_notification ADD CONSTRAINT FK_F8D1BC1CBF396750 FOREIGN KEY (id) REFERENCES market_notification (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE market_notification_deal_offer');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_notification_deal_offer (id INT NOT NULL, commodity_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_9120E7FBB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE market_notification_deal_offer ADD CONSTRAINT FK_9120E7FBB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_notification_deal_offer ADD CONSTRAINT FK_9120E7FBBF396750 FOREIGN KEY (id) REFERENCES market_notification (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE market_notification_kit_agreement_notification');
    }
}
