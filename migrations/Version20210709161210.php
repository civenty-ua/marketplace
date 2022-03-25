<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210709161210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dead_url ADD check_sum INT DEFAULT NULL');
        $this->addSql('CREATE INDEX url_checksum_idx ON dead_url (check_sum)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dead_url DROP check_sum');
        $this->addSql('CREATE INDEX url_checksum_idx ON dead_url (check_sum)');
    }
}
