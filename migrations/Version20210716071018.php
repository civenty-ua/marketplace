<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716071018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A66126F525E');
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A66A76ED395');
        $this->addSql('DROP INDEX UNIQ_ADF25A66126F525E ON item_registration');
        $this->addSql('DROP INDEX UNIQ_ADF25A66A76ED395 ON item_registration');
        $this->addSql('ALTER TABLE item_registration ADD user_id_id INT DEFAULT NULL, ADD item_id_id INT DEFAULT NULL, DROP user_id, DROP item_id');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A669D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A6655E38587 FOREIGN KEY (item_id_id) REFERENCES item (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A669D86650F ON item_registration (user_id_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A6655E38587 ON item_registration (item_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A669D86650F');
        $this->addSql('ALTER TABLE item_registration DROP FOREIGN KEY FK_ADF25A6655E38587');
        $this->addSql('DROP INDEX UNIQ_ADF25A669D86650F ON item_registration');
        $this->addSql('DROP INDEX UNIQ_ADF25A6655E38587 ON item_registration');
        $this->addSql('ALTER TABLE item_registration ADD user_id INT DEFAULT NULL, ADD item_id INT DEFAULT NULL, DROP user_id_id, DROP item_id_id');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A66126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item_registration ADD CONSTRAINT FK_ADF25A66A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A66126F525E ON item_registration (item_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADF25A66A76ED395 ON item_registration (user_id)');
    }
}
