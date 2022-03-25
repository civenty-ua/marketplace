<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210812060734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_to_user_feedback (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, target_user_id INT NOT NULL, feedback_form_id INT NOT NULL, INDEX IDX_C000E9D9A76ED395 (user_id), INDEX IDX_C000E9D96C066AFE (target_user_id), INDEX IDX_C000E9D9CEAFBBDE (feedback_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_to_user_feedback_answer (id INT AUTO_INCREMENT NOT NULL, user_feedback_id INT NOT NULL, question_id INT NOT NULL, answer LONGTEXT DEFAULT NULL, INDEX IDX_5AB34F7C7B526112 (user_feedback_id), INDEX IDX_5AB34F7C1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_to_user_feedback ADD CONSTRAINT FK_C000E9D9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_to_user_feedback ADD CONSTRAINT FK_C000E9D96C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_to_user_feedback ADD CONSTRAINT FK_C000E9D9CEAFBBDE FOREIGN KEY (feedback_form_id) REFERENCES feedback_form (id)');
        $this->addSql('ALTER TABLE user_to_user_feedback_answer ADD CONSTRAINT FK_5AB34F7C7B526112 FOREIGN KEY (user_feedback_id) REFERENCES user_to_user_feedback (id)');
        $this->addSql('ALTER TABLE user_to_user_feedback_answer ADD CONSTRAINT FK_5AB34F7C1E27F6BF FOREIGN KEY (question_id) REFERENCES feedback_form_question (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_to_user_feedback_answer DROP FOREIGN KEY FK_5AB34F7C7B526112');
        $this->addSql('DROP TABLE user_to_user_feedback');
        $this->addSql('DROP TABLE user_to_user_feedback_answer');
    }
}
