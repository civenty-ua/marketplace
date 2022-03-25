<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210618123837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A66A76ED395');
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A66126F525E');
        $this->addSql('DROP INDEX UNIQ_ADF25A66A76ED395 ON item_registration');
        $this->addSql('DROP INDEX UNIQ_ADF25A66126F525E ON item_registration');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A66A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A66126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A66A76ED395 ON item_registration (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A66126F525E ON item_registration (item_id)');

    }
}
