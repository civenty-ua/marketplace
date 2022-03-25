<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102041249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE text_blocks (id INT AUTO_INCREMENT NOT NULL, text_type_id INT NOT NULL, symbol_code VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, text_descrtiption VARCHAR(255) NOT NULL, INDEX IDX_DB610C8BE1EDA3AC (text_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE text_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE text_blocks ADD CONSTRAINT FK_DB610C8BE1EDA3AC FOREIGN KEY (text_type_id) REFERENCES text_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE text_blocks DROP FOREIGN KEY FK_DB610C8BE1EDA3AC');
        $this->addSql('DROP TABLE text_blocks');
        $this->addSql('DROP TABLE text_type');
    }
}
