<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211001134444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE occurrence (id INT NOT NULL, video_item_id INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, INDEX IDX_BEFD81F3F782BBF3 (video_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occurrence_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, short LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_ABC3D0C2C2AC5D3 (translatable_id), UNIQUE INDEX occurrence_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE occurrence ADD CONSTRAINT FK_BEFD81F3F782BBF3 FOREIGN KEY (video_item_id) REFERENCES video_item (id)');
        $this->addSql('ALTER TABLE occurrence ADD CONSTRAINT FK_BEFD81F3BF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE occurrence_translation ADD CONSTRAINT FK_ABC3D0C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES occurrence (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE occurrence_translation DROP FOREIGN KEY FK_ABC3D0C2C2AC5D3');
        $this->addSql('DROP TABLE occurrence');
        $this->addSql('DROP TABLE occurrence_translation');
    }
}
