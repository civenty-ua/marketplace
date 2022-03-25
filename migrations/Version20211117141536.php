<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211117141536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_to_user_review DROP rate');
        $this->addSql('CREATE TABLE user_to_user_rate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, target_user_id INT NOT NULL, rate DOUBLE PRECISION DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_1402D883A76ED395 (user_id), INDEX IDX_1402D8836C066AFE (target_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_to_user_rate ADD CONSTRAINT FK_1402D883A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_to_user_rate ADD CONSTRAINT FK_1402D8836C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_notification_offer_review ADD sender_is_rated TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_to_user_review ADD rate DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('DROP TABLE user_to_user_rate');
        $this->addSql('ALTER TABLE market_notification_offer_review DROP sender_is_rated');
    }
}
