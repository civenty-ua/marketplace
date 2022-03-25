<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102152843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_to_user_review (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, target_user_id INT NOT NULL, review_text LONGTEXT DEFAULT NULL, rate DOUBLE PRECISION DEFAULT NULL, INDEX IDX_59077BC1A76ED395 (user_id), INDEX IDX_59077BC16C066AFE (target_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_to_user_review ADD CONSTRAINT FK_59077BC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_to_user_review ADD CONSTRAINT FK_59077BC16C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_notification_offer_review ADD user_to_user_review_id INT NOT NULL');
        $this->addSql('ALTER TABLE market_notification_offer_review ADD CONSTRAINT FK_BF33844AA4830C80 FOREIGN KEY (user_to_user_review_id) REFERENCES user_to_user_review (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF33844AA4830C80 ON market_notification_offer_review (user_to_user_review_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_notification_offer_review DROP FOREIGN KEY FK_BF33844AA4830C80');
        $this->addSql('DROP TABLE user_to_user_review');
        $this->addSql('DROP INDEX UNIQ_BF33844AA4830C80 ON market_notification_offer_review');
        $this->addSql('ALTER TABLE market_notification_offer_review DROP user_to_user_review_id');
    }
}
