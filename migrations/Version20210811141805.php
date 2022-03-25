<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210811141805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feedback_answer DROP FOREIGN KEY FK_F54D12E47B526112');
        $this->addSql('CREATE TABLE user_item_feedback (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, feedback_form_id INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_50AA21C3A76ED395 (user_id), INDEX IDX_50AA21C3126F525E (item_id), INDEX IDX_50AA21C3CEAFBBDE (feedback_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_item_feedback_answer (id INT AUTO_INCREMENT NOT NULL, user_feedback_id INT NOT NULL, feedback_form_question_id INT NOT NULL, answer LONGTEXT DEFAULT NULL, is_active TINYINT(1) DEFAULT NULL, INDEX IDX_E37176B67B526112 (user_feedback_id), INDEX IDX_E37176B6AD64D353 (feedback_form_question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_item_feedback ADD CONSTRAINT FK_50AA21C3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_item_feedback ADD CONSTRAINT FK_50AA21C3126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE user_item_feedback ADD CONSTRAINT FK_50AA21C3CEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id)');
        $this->addSql('ALTER TABLE user_item_feedback_answer ADD CONSTRAINT FK_E37176B67B526112 FOREIGN KEY (user_feedback_id) REFERENCES user_item_feedback (id)');
        $this->addSql('ALTER TABLE user_item_feedback_answer ADD CONSTRAINT FK_E37176B6AD64D353 FOREIGN KEY (feedback_form_question_id) REFERENCES feedback_form_question (id)');
        $this->addSql('DROP TABLE user_feedback');
        $this->addSql('DROP TABLE user_feedback_answer');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_item_feedback_answer DROP FOREIGN KEY FK_E37176B67B526112');
        $this->addSql('CREATE TABLE user_feedback (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, feedback_form_id INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_32A4A058126F525E (item_id), INDEX IDX_32A4A058A76ED395 (user_id), INDEX IDX_32A4A058CEAFBBDE (feedback_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_feedback_answer (id INT AUTO_INCREMENT NOT NULL, user_feedback_id INT NOT NULL, feedback_form_question_id INT NOT NULL, answer LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_active TINYINT(1) DEFAULT NULL, INDEX IDX_F54D12E47B526112 (user_feedback_id), INDEX IDX_F54D12E4AD64D353 (feedback_form_question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058CEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E47B526112 FOREIGN KEY (user_feedback_id) REFERENCES user_feedback (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_feedback_answer ADD CONSTRAINT FK_F54D12E4AD64D353 FOREIGN KEY (feedback_form_question_id) REFERENCES feedback_form_question (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE user_item_feedback');
        $this->addSql('DROP TABLE user_item_feedback_answer');
    }
}
