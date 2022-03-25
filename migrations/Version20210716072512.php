<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716072512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A6655E38587, ADD INDEX IDX_ADF25A6655E38587 (item_id_id)');
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A669D86650F, ADD INDEX IDX_ADF25A669D86650F (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_registration DROP INDEX IDX_ADF25A669D86650F, ADD UNIQUE INDEX UNIQ_ADF25A669D86650F (user_id_id)');
        $this->addSql('ALTER TABLE item_registration DROP INDEX IDX_ADF25A6655E38587, ADD UNIQUE INDEX UNIQ_ADF25A6655E38587 (item_id_id)');
    }
}
