<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610113227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type ENUM(\'string\', \'number\')');
        $this->addSql('DROP INDEX IDX_45B0AE642C2AC5D3 ON lesson_translation');
        $this->addSql('ALTER TABLE lesson_translation DROP FOREIGN KEY FK_45B0AE642C2AC5D3');

        $this->addSql('ALTER TABLE lesson_module_lesson DROP FOREIGN KEY FK_865B273ECDF80196');
        $this->addSql('DROP INDEX IDX_865B273ECDF80196 ON lesson_module_lesson');

        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3BF396750');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3F782BBF3');
        $this->addSql('DROP INDEX IDX_F87474F3F782BBF3 ON lesson');

        $this->addSql('ALTER TABLE lesson CHANGE id id INT NOT NULL');

        $this->addSql('CREATE INDEX IDX_F87474F3F782BBF3 ON lesson (video_item_id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3BF396750 FOREIGN KEY (id) REFERENCES course_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3F782BBF3 FOREIGN KEY (video_item_id) REFERENCES video_item (id)');

        $this->addSql('ALTER TABLE lesson_translation ADD CONSTRAINT FK_45B0AE642C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_45B0AE642C2AC5D3 ON lesson_translation (translatable_id)');

        $this->addSql('ALTER TABLE lesson_module_lesson ADD CONSTRAINT FK_865B273ECDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_865B273ECDF80196 ON lesson_module_lesson (lesson_id)');



    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_form_question CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX IDX_45B0AE642C2AC5D3 ON lesson_translation');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_45B0AE642C2AC5D3');
        $this->addSql('ALTER TABLE lesson CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE lesson_translation ADD CONSTRAINT FK_45B0AE642C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_45B0AE642C2AC5D3 ON lesson_translation (translatable_id)');
    }
}
