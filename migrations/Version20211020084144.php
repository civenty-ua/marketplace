<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020084144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE type_page_translation set name = "Eco-articles" where name like "Eco articles"');
        $this->addSql('UPDATE type_page_translation set name = "Еко-статті" where name like "Еко статті"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE type_page_translation set name = "Eco articles" where name like "Eco-articles"');
        $this->addSql('UPDATE type_page_translation set name = "Еко статті" where name like "Еко-статті"');
    }
}
