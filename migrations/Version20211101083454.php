<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211101083454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_commodity_product_certificate (commodity_product_id INT NOT NULL, user_certificate_id INT NOT NULL, INDEX IDX_910FC1D1D4E9E2 (commodity_product_id), INDEX IDX_910FC1DF2A7E0A4 (user_certificate_id), PRIMARY KEY(commodity_product_id, user_certificate_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_commodity_product_certificate ADD CONSTRAINT FK_910FC1D1D4E9E2 FOREIGN KEY (commodity_product_id) REFERENCES market_commodity_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_commodity_product_certificate ADD CONSTRAINT FK_910FC1DF2A7E0A4 FOREIGN KEY (user_certificate_id) REFERENCES market_user_certificate (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE market_commodity_product_certificate');
    }
}
