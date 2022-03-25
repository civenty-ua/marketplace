<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622074902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6698260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('CREATE INDEX IDX_23A0E6698260155 ON article (region_id)');
        $this->addSql('ALTER TABLE expert_translation DROP role');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE item_registration DROP INDEX FK_ADF25A66126F525E, ADD UNIQUE INDEX UNIQ_ADF25A66126F525E (item_id)');
        $this->addSql('ALTER TABLE item_registration DROP INDEX FK_ADF25A66A76ED395, ADD UNIQUE INDEX UNIQ_ADF25A66A76ED395 (user_id)');
//        $this->addSql('ALTER TABLE partner DROP social_networks');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6698260155');
        $this->addSql('DROP INDEX IDX_23A0E6698260155 ON article');
        $this->addSql('ALTER TABLE article DROP region_id');
        $this->addSql('ALTER TABLE expert ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE expert_translation ADD role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A66A76ED395, ADD INDEX FK_ADF25A66A76ED395 (user_id)');
        $this->addSql('ALTER TABLE item_registration DROP INDEX UNIQ_ADF25A66126F525E, ADD INDEX FK_ADF25A66126F525E (item_id)');
//        $this->addSql('ALTER TABLE partner ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
