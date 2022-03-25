<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210831084349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webinar_estimation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, webinar_id INT DEFAULT NULL, INDEX IDX_C677E722A76ED395 (user_id), INDEX IDX_C677E722A391D86E (webinar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE webinar_estimation ADD CONSTRAINT FK_C677E722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE webinar_estimation ADD CONSTRAINT FK_C677E722A391D86E FOREIGN KEY (webinar_id) REFERENCES item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE webinar_estimation');
    }
}
