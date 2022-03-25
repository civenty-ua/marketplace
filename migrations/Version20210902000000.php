<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210902000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $existAdmin     = 'admin@test.com';
        $superAdminRole = User::ROLE_SUPER_ADMIN;

        $this->addSql("
            UPDATE user
            SET    roles = '[\"$superAdminRole\"]'
            WHERE  email = '$existAdmin'
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
