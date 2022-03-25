<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210908115255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_property DROP FOREIGN KEY FK_6B7FF8DEE51E9644');
        $this->addSql('ALTER TABLE locality DROP FOREIGN KEY FK_E1D6B8E6B08FA272');
        $this->addSql('ALTER TABLE user_property DROP FOREIGN KEY FK_6B7FF8DEB08FA272');
        $this->addSql('ALTER TABLE user_property DROP FOREIGN KEY FK_6B7FF8DEAA00EA09');
        $this->addSql('ALTER TABLE user_property DROP FOREIGN KEY FK_6B7FF8DE88823A92');
        $this->addSql('ALTER TABLE phone DROP FOREIGN KEY FK_444F97DDFD89DA79');
        $this->addSql('CREATE TABLE market_company_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type_role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_district (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_DC70414F98260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_legal_company_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_locality (id INT AUTO_INCREMENT NOT NULL, district_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C67AD2EB08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_phone (id INT AUTO_INCREMENT NOT NULL, user_property_id INT NOT NULL, phone VARCHAR(255) NOT NULL, is_main TINYINT(1) NOT NULL, is_telegram TINYINT(1) DEFAULT NULL, is_viber TINYINT(1) DEFAULT NULL, is_whats_app TINYINT(1) DEFAULT NULL, INDEX IDX_8F6128A8FD89DA79 (user_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_user_property (id INT AUTO_INCREMENT NOT NULL, company_type_id INT NOT NULL, district_id INT DEFAULT NULL, locality_id INT DEFAULT NULL, legal_company_type_id INT DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, facebook_link VARCHAR(255) DEFAULT NULL, instagram_link VARCHAR(255) DEFAULT NULL, INDEX IDX_FA91E5EFE51E9644 (company_type_id), INDEX IDX_FA91E5EFB08FA272 (district_id), INDEX IDX_FA91E5EF88823A92 (locality_id), INDEX IDX_FA91E5EFAA00EA09 (legal_company_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_district ADD CONSTRAINT FK_DC70414F98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE market_locality ADD CONSTRAINT FK_C67AD2EB08FA272 FOREIGN KEY (district_id) REFERENCES market_district (id)');
        $this->addSql('ALTER TABLE market_phone ADD CONSTRAINT FK_8F6128A8FD89DA79 FOREIGN KEY (user_property_id) REFERENCES market_user_property (id)');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFE51E9644 FOREIGN KEY (company_type_id) REFERENCES market_company_type (id)');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFB08FA272 FOREIGN KEY (district_id) REFERENCES market_district (id)');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EF88823A92 FOREIGN KEY (locality_id) REFERENCES market_locality (id)');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFAA00EA09 FOREIGN KEY (legal_company_type_id) REFERENCES market_legal_company_type (id)');
        $this->addSql('DROP TABLE company_type');
        $this->addSql('DROP TABLE district');
        $this->addSql('DROP TABLE legal_company_type');
        $this->addSql('DROP TABLE locality');
        $this->addSql('DROP TABLE phone');
        $this->addSql('DROP TABLE user_property');
        $this->addSql('ALTER TABLE user ADD user_property_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FD89DA79 FOREIGN KEY (user_property_id) REFERENCES market_user_property (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649FD89DA79 ON user (user_property_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFE51E9644');
        $this->addSql('ALTER TABLE market_locality DROP FOREIGN KEY FK_C67AD2EB08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFB08FA272');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFAA00EA09');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EF88823A92');
        $this->addSql('ALTER TABLE market_phone DROP FOREIGN KEY FK_8F6128A8FD89DA79');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FD89DA79');
        $this->addSql('CREATE TABLE company_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type_role VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE district (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_31C1548798260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE legal_company_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE locality (id INT AUTO_INCREMENT NOT NULL, district_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E1D6B8E6B08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE phone (id INT AUTO_INCREMENT NOT NULL, user_property_id INT NOT NULL, phone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_main TINYINT(1) NOT NULL, is_telegram TINYINT(1) DEFAULT NULL, is_viber TINYINT(1) DEFAULT NULL, is_whats_app TINYINT(1) DEFAULT NULL, INDEX IDX_444F97DDFD89DA79 (user_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_property (id INT AUTO_INCREMENT NOT NULL, company_type_id INT NOT NULL, district_id INT DEFAULT NULL, locality_id INT DEFAULT NULL, legal_company_type_id INT DEFAULT NULL, company_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, facebook_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, instagram_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_6B7FF8DE88823A92 (locality_id), INDEX IDX_6B7FF8DEAA00EA09 (legal_company_type_id), INDEX IDX_6B7FF8DEB08FA272 (district_id), INDEX IDX_6B7FF8DEE51E9644 (company_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE district ADD CONSTRAINT FK_31C1548798260155 FOREIGN KEY (region_id) REFERENCES region (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE locality ADD CONSTRAINT FK_E1D6B8E6B08FA272 FOREIGN KEY (district_id) REFERENCES district (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE phone ADD CONSTRAINT FK_444F97DDFD89DA79 FOREIGN KEY (user_property_id) REFERENCES user_property (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_property ADD CONSTRAINT FK_6B7FF8DE88823A92 FOREIGN KEY (locality_id) REFERENCES locality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_property ADD CONSTRAINT FK_6B7FF8DEAA00EA09 FOREIGN KEY (legal_company_type_id) REFERENCES legal_company_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_property ADD CONSTRAINT FK_6B7FF8DEB08FA272 FOREIGN KEY (district_id) REFERENCES district (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_property ADD CONSTRAINT FK_6B7FF8DEE51E9644 FOREIGN KEY (company_type_id) REFERENCES company_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE market_company_type');
        $this->addSql('DROP TABLE market_district');
        $this->addSql('DROP TABLE market_legal_company_type');
        $this->addSql('DROP TABLE market_locality');
        $this->addSql('DROP TABLE market_phone');
        $this->addSql('DROP TABLE market_user_property');
        $this->addSql('DROP INDEX UNIQ_8D93D649FD89DA79 ON user');
        $this->addSql('ALTER TABLE user DROP user_property_id');
    }
}
