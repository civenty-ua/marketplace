<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018112245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news_item (news_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_CAC6D395B5A459A0 (news_id), INDEX IDX_CAC6D395126F525E (item_id), PRIMARY KEY(news_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news_item ADD CONSTRAINT FK_CAC6D395B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_item ADD CONSTRAINT FK_CAC6D395126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE news_news');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news_news (news_source INT NOT NULL, news_target INT NOT NULL, INDEX IDX_C80E6FDBCAC6EC88 (news_target), INDEX IDX_C80E6FDBD323BC07 (news_source), PRIMARY KEY(news_source, news_target)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE news_news ADD CONSTRAINT FK_C80E6FDBCAC6EC88 FOREIGN KEY (news_target) REFERENCES news (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_news ADD CONSTRAINT FK_C80E6FDBD323BC07 FOREIGN KEY (news_source) REFERENCES news (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE news_item');
    }
}
