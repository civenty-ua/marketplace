<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006111955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news (id INT NOT NULL, region_id INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, INDEX IDX_1DD3995098260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_news (news_source INT NOT NULL, news_target INT NOT NULL, INDEX IDX_C80E6FDBD323BC07 (news_source), INDEX IDX_C80E6FDBCAC6EC88 (news_target), PRIMARY KEY(news_source, news_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, short LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_9D5CF3202C2AC5D3 (translatable_id), UNIQUE INDEX news_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD3995098260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950BF396750 FOREIGN KEY (id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_news ADD CONSTRAINT FK_C80E6FDBD323BC07 FOREIGN KEY (news_source) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_news ADD CONSTRAINT FK_C80E6FDBCAC6EC88 FOREIGN KEY (news_target) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_translation ADD CONSTRAINT FK_9D5CF3202C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES news (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_news DROP FOREIGN KEY FK_C80E6FDBD323BC07');
        $this->addSql('ALTER TABLE news_news DROP FOREIGN KEY FK_C80E6FDBCAC6EC88');
        $this->addSql('ALTER TABLE news_translation DROP FOREIGN KEY FK_9D5CF3202C2AC5D3');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE news_news');
        $this->addSql('DROP TABLE news_translation');
    }
}
