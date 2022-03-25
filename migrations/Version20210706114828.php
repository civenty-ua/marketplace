<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210706114828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partner ADD zoom_api_key VARCHAR(255) DEFAULT NULL, ADD zoom_client_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE webinar ADD use_partner_api_keys TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webinar DROP zoom_api_key, DROP zoom_client_secret');
        $this->addSql('ALTER TABLE webinar DROP use_partner_api_keys');
    }
}
