<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913095734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_locality DROP FOREIGN KEY FK_C67AD2EB08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFB08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EF88823A92');
        $this->addSql('CREATE TABLE district (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_31C1548798260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE locality (id INT AUTO_INCREMENT NOT NULL, district_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_E1D6B8E6B08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE district ADD CONSTRAINT FK_31C1548798260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE locality ADD CONSTRAINT FK_E1D6B8E6B08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
        $this->addSql('DROP TABLE market_district');
        $this->addSql('DROP TABLE market_locality');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EF88823A92 FOREIGN KEY (locality_id) REFERENCES locality (id)');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFB08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE locality DROP FOREIGN KEY FK_E1D6B8E6B08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFB08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EF88823A92');
        $this->addSql('CREATE TABLE market_district (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_DC70414F98260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE market_locality (id INT AUTO_INCREMENT NOT NULL, district_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C67AD2EB08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE market_district ADD CONSTRAINT FK_DC70414F98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_locality ADD CONSTRAINT FK_C67AD2EB08FA272 FOREIGN KEY (district_id) REFERENCES market_district (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE district');
        $this->addSql('DROP TABLE locality');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFB08FA272 FOREIGN KEY (district_id) REFERENCES market_district (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EF88823A92 FOREIGN KEY (locality_id) REFERENCES market_locality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
