<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102044522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE text_blocks DROP FOREIGN KEY FK_DB610C8BE1EDA3AC');
        $this->addSql('DROP INDEX IDX_DB610C8BE1EDA3AC ON text_blocks');
        $this->addSql('ALTER TABLE text_blocks CHANGE text_type_id text_type_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE text_blocks ADD CONSTRAINT FK_DB610C8BEDB2103A FOREIGN KEY (text_type_id_id) REFERENCES text_type (id)');
        $this->addSql('CREATE INDEX IDX_DB610C8BEDB2103A ON text_blocks (text_type_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE text_blocks DROP FOREIGN KEY FK_DB610C8BEDB2103A');
        $this->addSql('DROP INDEX IDX_DB610C8BEDB2103A ON text_blocks');
        $this->addSql('ALTER TABLE text_blocks CHANGE text_type_id_id text_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE text_blocks ADD CONSTRAINT FK_DB610C8BE1EDA3AC FOREIGN KEY (text_type_id) REFERENCES text_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DB610C8BE1EDA3AC ON text_blocks (text_type_id)');
    }
}
