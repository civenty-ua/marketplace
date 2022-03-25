<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622074827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE expert DROP social_networks');
//        $this->addSql('ALTER TABLE expert_translation DROP role');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
//        $this->addSql('ALTER TABLE item_registration DROP INDEX FK_ADF25A66126F525E, ADD UNIQUE INDEX UNIQ_ADF25A66126F525E (item_id)');
//        $this->addSql('ALTER TABLE item_registration DROP INDEX FK_ADF25A66A76ED395, ADD UNIQUE INDEX UNIQ_ADF25A66A76ED395 (user_id)');
        $this->addSql('ALTER TABLE lesson_module ADD start_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partner DROP social_networks');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expert ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE expert_translation ADD role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A66A76ED395, ADD INDEX FK_ADF25A66A76ED395 (user_id)');
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A66126F525E, ADD INDEX FK_ADF25A66126F525E (item_id)');
        $this->addSql('ALTER TABLE lesson_module DROP start_date');
        $this->addSql('ALTER TABLE partner ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
