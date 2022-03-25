<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210630000149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_download (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, ext VARCHAR(10) NOT NULL, link VARCHAR(600) NOT NULL, downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_98976670A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_viewed (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT DEFAULT NULL, viewed_at DATETIME NOT NULL, INDEX IDX_92DA0239A76ED395 (user_id), INDEX IDX_92DA0239126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_download ADD CONSTRAINT FK_98976670A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_viewed ADD CONSTRAINT FK_92DA0239A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_viewed ADD CONSTRAINT FK_92DA0239126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_download');
        $this->addSql('DROP TABLE user_viewed');
        $this->addSql('ALTER TABLE expert ADD social_networks VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
