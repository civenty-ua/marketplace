<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210630125802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A0585FF69B7D');
        $this->addSql('DROP INDEX IDX_32A4A0585FF69B7D ON user_feedback');
        $this->addSql('ALTER TABLE user_feedback DROP comment, CHANGE form_id feedback_form_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058CEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id)');
        $this->addSql('CREATE INDEX IDX_32A4A058CEAFBBDE ON user_feedback (feedback_form_id)');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E41E27F6BF');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E4D249A887');
        $this->addSql('DROP INDEX IDX_F54D12E41E27F6BF ON user_feedback_answer');
        $this->addSql('DROP INDEX IDX_F54D12E4D249A887 ON user_feedback_answer');
        $this->addSql('ALTER TABLE user_feedback_answer ADD user_feedback_id INT NOT NULL, ADD feedback_form_question_id INT NOT NULL, DROP feedback_id, DROP question_id');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E47B526112 FOREIGN KEY (user_feedback_id) REFERENCES user_feedback (id)');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E4AD64D353 FOREIGN KEY (feedback_form_question_id) REFERENCES feedback_form_question (id)');
        $this->addSql('CREATE INDEX IDX_F54D12E47B526112 ON user_feedback_answer (user_feedback_id)');
        $this->addSql('CREATE INDEX IDX_F54D12E4AD64D353 ON user_feedback_answer (feedback_form_question_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A058CEAFBBDE');
        $this->addSql('DROP INDEX IDX_32A4A058CEAFBBDE ON user_feedback');
        $this->addSql('ALTER TABLE user_feedback ADD comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE feedback_form_id form_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A0585FF69B7D FOREIGN KEY (form_id) REFERENCES feedback_form (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_32A4A0585FF69B7D ON user_feedback (form_id)');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E47B526112');
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E4AD64D353');
        $this->addSql('DROP INDEX IDX_F54D12E47B526112 ON user_feedback_answer');
        $this->addSql('DROP INDEX IDX_F54D12E4AD64D353 ON user_feedback_answer');
        $this->addSql('ALTER TABLE user_feedback_answer ADD feedback_id INT NOT NULL, ADD question_id INT NOT NULL, DROP user_feedback_id, DROP feedback_form_question_id');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E41E27F6BF FOREIGN KEY (question_id) REFERENCES feedback_form_question (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E4D249A887 FOREIGN KEY (feedback_id) REFERENCES user_feedback (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F54D12E41E27F6BF ON user_feedback_answer (question_id)');
        $this->addSql('CREATE INDEX IDX_F54D12E4D249A887 ON user_feedback_answer (feedback_id)');
    }
}
