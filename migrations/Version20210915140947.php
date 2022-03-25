<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210915140947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dead_url CHANGE is_active is_active TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE item CHANGE is_active is_active TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_item_feedback_answer CHANGE is_active is_active TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dead_url CHANGE is_active is_active TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE item CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user_item_feedback_answer CHANGE is_active is_active TINYINT(1) DEFAULT NULL');
    }
}
