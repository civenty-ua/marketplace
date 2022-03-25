<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210607094404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE other (id INT NOT NULL, video_item_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_D9583520F782BBF3 (video_item_id), INDEX IDX_D958352012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE other_tags (other_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_6F8C2F4998D9879 (other_id), INDEX IDX_6F8C2F48D7B4FB4 (tags_id), PRIMARY KEY(other_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE other_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, short LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_B9BFFAE92C2AC5D3 (translatable_id), UNIQUE INDEX other_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE other ADD CONSTRAINT FK_D9583520F782BBF3 FOREIGN KEY (video_item_id) REFERENCES video_item (id)');
        $this->addSql('ALTER TABLE other ADD CONSTRAINT FK_D958352012469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE other ADD CONSTRAINT FK_D9583520BF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE other_tags ADD CONSTRAINT FK_6F8C2F4998D9879 FOREIGN KEY (other_id) REFERENCES other (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE other_tags ADD CONSTRAINT FK_6F8C2F48D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE other_translation ADD CONSTRAINT FK_B9BFFAE92C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES other (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE other_tags DROP FOREIGN KEY FK_6F8C2F4998D9879');
        $this->addSql('ALTER TABLE other_translation DROP FOREIGN KEY FK_B9BFFAE92C2AC5D3');
        $this->addSql('DROP TABLE other');
        $this->addSql('DROP TABLE other_tags');
        $this->addSql('DROP TABLE other_translation');
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
