<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617081814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expert ADD facebook VARCHAR(255) DEFAULT NULL, ADD twitter VARCHAR(255) DEFAULT NULL, ADD youtube VARCHAR(255) DEFAULT NULL, ADD telegram VARCHAR(255) DEFAULT NULL, ADD instagram VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE partner ADD facebook VARCHAR(255) DEFAULT NULL, ADD twitter VARCHAR(255) DEFAULT NULL, ADD youtube VARCHAR(255) DEFAULT NULL, ADD telegram VARCHAR(255) DEFAULT NULL, ADD instagram VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expert DROP facebook, DROP twitter, DROP youtube, DROP telegram, DROP instagram');
        $this->addSql('ALTER TABLE partner DROP facebook, DROP twitter, DROP youtube, DROP telegram, DROP instagram');
    }
}
