<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210611132451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE expert_expert_type (expert_id INT NOT NULL, expert_type_id INT NOT NULL, INDEX IDX_27DAC707C5568CE4 (expert_id), INDEX IDX_27DAC707B72EF91F (expert_type_id), PRIMARY KEY(expert_id, expert_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_type (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_type_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_C1620F572C2AC5D3 (translatable_id), UNIQUE INDEX expert_type_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expert_expert_type ADD CONSTRAINT FK_27DAC707C5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expert_expert_type ADD CONSTRAINT FK_27DAC707B72EF91F FOREIGN KEY (expert_type_id) REFERENCES expert_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expert_type_translation ADD CONSTRAINT FK_C1620F572C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES expert_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expert_expert_type DROP FOREIGN KEY FK_27DAC707B72EF91F');
        $this->addSql('ALTER TABLE expert_type_translation DROP FOREIGN KEY FK_C1620F572C2AC5D3');
        $this->addSql('DROP TABLE expert_expert_type');
        $this->addSql('DROP TABLE expert_type');
        $this->addSql('DROP TABLE expert_type_translation');
    }
}
