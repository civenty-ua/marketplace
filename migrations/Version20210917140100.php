<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210917140100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_commodity ADD CONSTRAINT FK_CBDFD4E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_CBDFD4E3A76ED395 ON market_commodity (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_commodity DROP FOREIGN KEY FK_CBDFD4E3A76ED395');
        $this->addSql('DROP INDEX IDX_CBDFD4E3A76ED395 ON market_commodity');
        $this->addSql('ALTER TABLE market_commodity DROP user_id');
    }
}
