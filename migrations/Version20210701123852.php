<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701123852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activity_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_BAE72F632C2AC5D3 (translatable_id), UNIQUE INDEX activity_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity_translation ADD CONSTRAINT FK_BAE72F632C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE user ADD activity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64981C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64981C06096 ON user (activity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_translation DROP FOREIGN KEY FK_BAE72F632C2AC5D3');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64981C06096');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE activity_translation');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX IDX_8D93D64981C06096 ON user');
        $this->addSql('ALTER TABLE user DROP activity_id');
    }
}
