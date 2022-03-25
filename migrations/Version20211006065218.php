<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006065218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_user_favorite (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_favorite_id INT NOT NULL, INDEX IDX_19A677E8A76ED395 (user_id), INDEX IDX_19A677E830C8188 (user_favorite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_user_favorite ADD CONSTRAINT FK_19A677E8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_user_favorite ADD CONSTRAINT FK_19A677E830C8188 FOREIGN KEY (user_favorite_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE market_user_favorite');
    }
}
