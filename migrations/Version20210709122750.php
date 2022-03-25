<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210709122750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_crop MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user_crop DROP FOREIGN KEY FK_97437152888579EE');
        $this->addSql('ALTER TABLE user_crop DROP FOREIGN KEY FK_97437152A76ED395');
        $this->addSql('ALTER TABLE user_crop DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user_crop DROP id');
        $this->addSql('ALTER TABLE user_crop ADD CONSTRAINT FK_97437152888579EE FOREIGN KEY (crop_id) REFERENCES crop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_crop ADD CONSTRAINT FK_97437152A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_crop ADD PRIMARY KEY (user_id, crop_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_crop DROP FOREIGN KEY FK_97437152A76ED395');
        $this->addSql('ALTER TABLE user_crop DROP FOREIGN KEY FK_97437152888579EE');
        $this->addSql('ALTER TABLE user_crop ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_crop ADD CONSTRAINT FK_97437152A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_crop ADD CONSTRAINT FK_97437152888579EE FOREIGN KEY (crop_id) REFERENCES crop (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
