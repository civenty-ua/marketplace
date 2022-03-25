<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210906134841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_category_attribute_parameters (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, attribute_id INT NOT NULL, required TINYINT(1) DEFAULT NULL, show_on_list TINYINT(1) DEFAULT NULL, sort INT NOT NULL, INDEX IDX_B7A9C24B12469DE2 (category_id), INDEX IDX_B7A9C24BB6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_category_attribute_parameters ADD CONSTRAINT FK_B7A9C24B12469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id)');
        $this->addSql('ALTER TABLE market_category_attribute_parameters ADD CONSTRAINT FK_B7A9C24BB6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id)');
        $this->addSql('DROP TABLE category_attribute');
        $this->addSql('ALTER TABLE market_category_attribute DROP required');
        $this->addSql('ALTER TABLE market_category_attribute_list_value DROP FOREIGN KEY FK_75DE468212469DE2');
        $this->addSql('ALTER TABLE market_category_attribute_list_value DROP FOREIGN KEY FK_75DE4682B6E62EFA');
        $this->addSql('DROP INDEX IDX_75DE468212469DE2 ON market_category_attribute_list_value');
        $this->addSql('DROP INDEX IDX_75DE4682B6E62EFA ON market_category_attribute_list_value');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD category_attribute_id INT NOT NULL, DROP attribute_id, DROP category_id, CHANGE title value VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD CONSTRAINT FK_75DE46826C310D68 FOREIGN KEY (category_attribute_id) REFERENCES market_category_attribute_parameters (id)');
        $this->addSql('CREATE INDEX IDX_75DE46826C310D68 ON market_category_attribute_list_value (category_attribute_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_category_attribute_list_value DROP FOREIGN KEY FK_75DE46826C310D68');
        $this->addSql('CREATE TABLE category_attribute (category_id INT NOT NULL, attribute_id INT NOT NULL, INDEX IDX_3D1A3DCB12469DE2 (category_id), INDEX IDX_3D1A3DCBB6E62EFA (attribute_id), PRIMARY KEY(category_id, attribute_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCB12469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCBB6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE market_category_attribute_parameters');
        $this->addSql('ALTER TABLE market_category_attribute ADD required TINYINT(1) DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_75DE46826C310D68 ON market_category_attribute_list_value');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD category_id INT NOT NULL, CHANGE category_attribute_id attribute_id INT NOT NULL, CHANGE value title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD CONSTRAINT FK_75DE468212469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD CONSTRAINT FK_75DE4682B6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_75DE468212469DE2 ON market_category_attribute_list_value (category_id)');
        $this->addSql('CREATE INDEX IDX_75DE4682B6E62EFA ON market_category_attribute_list_value (attribute_id)');
    }
}
