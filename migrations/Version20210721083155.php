<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210721083155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE expert_tags (expert_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_D8CDBE05C5568CE4 (expert_id), INDEX IDX_D8CDBE058D7B4FB4 (tags_id), PRIMARY KEY(expert_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expert_tags ADD CONSTRAINT FK_D8CDBE05C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expert_tags ADD CONSTRAINT FK_D8CDBE058D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE expert_tags');
    }
}
