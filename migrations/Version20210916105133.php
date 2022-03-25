<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210916105133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_phone DROP FOREIGN KEY FK_8F6128A8FD89DA79');
        $this->addSql('DROP INDEX IDX_8F6128A8FD89DA79 ON market_phone');
        $this->addSql('ALTER TABLE market_phone CHANGE user_property_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_phone ADD CONSTRAINT FK_8F6128A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F6128A8A76ED395 ON market_phone (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_phone DROP FOREIGN KEY FK_8F6128A8A76ED395');
        $this->addSql('DROP INDEX IDX_8F6128A8A76ED395 ON market_phone');
        $this->addSql('ALTER TABLE market_phone CHANGE user_id user_property_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_phone ADD CONSTRAINT FK_8F6128A8FD89DA79 FOREIGN KEY (user_property_id) REFERENCES market_user_property (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8F6128A8FD89DA79 ON market_phone (user_property_id)');
    }
}
