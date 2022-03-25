<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211014085645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_last_lesson_viewed (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, lesson_id INT DEFAULT NULL, course_id INT DEFAULT NULL, viewed_at DATETIME NOT NULL, INDEX IDX_44556C3FA76ED395 (user_id), INDEX IDX_44556C3FCDF80196 (lesson_id), INDEX IDX_44556C3F591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_last_lesson_viewed ADD CONSTRAINT FK_44556C3FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_last_lesson_viewed ADD CONSTRAINT FK_44556C3FCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE user_last_lesson_viewed ADD CONSTRAINT FK_44556C3F591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_last_lesson_viewed');
    }
}
