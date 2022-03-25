<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210831075348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'added count user in settings';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {

    }
}
