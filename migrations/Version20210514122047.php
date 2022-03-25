<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210514122047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('ALTER TABLE lesson ADD video_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3F782BBF3 FOREIGN KEY (video_item_id) REFERENCES video_item (id)');
        $this->addSql('CREATE INDEX IDX_F87474F3F782BBF3 ON lesson (video_item_id)');
        $this->addSql('ALTER TABLE video_item DROP FOREIGN KEY FK_188C78C2CDF80196');
        $this->addSql('DROP INDEX IDX_188C78C2CDF80196 ON video_item');
        $this->addSql('ALTER TABLE video_item DROP lesson_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3F782BBF3');
        $this->addSql('DROP INDEX IDX_F87474F3F782BBF3 ON lesson');
        $this->addSql('ALTER TABLE lesson DROP video_item_id');
        $this->addSql('ALTER TABLE video_item ADD lesson_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_item ADD CONSTRAINT FK_188C78C2CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_188C78C2CDF80196 ON video_item (lesson_id)');
    }
}
