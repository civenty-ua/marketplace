<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210713074322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_part DROP FOREIGN KEY FK_81ADADC044204E00');
        $this->addSql('DROP INDEX IDX_81ADADC044204E00 ON course_part');
        $this->addSql('ALTER TABLE course_part CHANGE course_part_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_part ADD CONSTRAINT FK_81ADADC0591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_81ADADC0591CC992 ON course_part (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_part DROP FOREIGN KEY FK_81ADADC0591CC992');
        $this->addSql('DROP INDEX IDX_81ADADC0591CC992 ON course_part');
        $this->addSql('ALTER TABLE course_part CHANGE course_id course_part_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_part ADD CONSTRAINT FK_81ADADC044204E00 FOREIGN KEY (course_part_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_81ADADC044204E00 ON course_part (course_part_id)');
    }
}
