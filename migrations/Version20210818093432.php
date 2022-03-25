<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818093432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_EBFD0C09727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_attribute (category_id INT NOT NULL, attribute_id INT NOT NULL, INDEX IDX_3D1A3DCB12469DE2 (category_id), INDEX IDX_3D1A3DCBB6E62EFA (attribute_id), PRIMARY KEY(category_id, attribute_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_category_attribute (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, required TINYINT(1) DEFAULT NULL, type VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, UNIQUE INDEX code_unique (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_category_attribute_list_value (id INT AUTO_INCREMENT NOT NULL, attribute_id INT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_75DE4682B6E62EFA (attribute_id), INDEX IDX_75DE468212469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_commodity (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, is_active TINYINT(1) DEFAULT NULL, active_from DATETIME NOT NULL, active_to DATETIME NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_CBDFD4E312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_commodity_attribute_value (id INT AUTO_INCREMENT NOT NULL, commodity_id INT NOT NULL, attribute_id INT NOT NULL, value LONGTEXT NOT NULL, INDEX IDX_8D406293B4ACC212 (commodity_id), INDEX IDX_8D406293B6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_commodity_kit (id INT NOT NULL, is_approved TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commodity_kit_commodity (commodity_kit_id INT NOT NULL, commodity_id INT NOT NULL, INDEX IDX_F1E8C8144FC4C805 (commodity_kit_id), INDEX IDX_F1E8C814B4ACC212 (commodity_id), PRIMARY KEY(commodity_kit_id, commodity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_commodity_product (id INT NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_commodity_service (id INT NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_kit_agreement (id INT AUTO_INCREMENT NOT NULL, kit_id INT NOT NULL, commodity_id INT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_CD3DAE93A8E60EF (kit_id), INDEX IDX_CD3DAE9B4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_notification (id INT AUTO_INCREMENT NOT NULL, sender_id INT DEFAULT NULL, receiver_id INT NOT NULL, is_read TINYINT(1) DEFAULT NULL, message LONGTEXT NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_3525D084F624B39D (sender_id), INDEX IDX_3525D084CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_notification_bit_offer (id INT NOT NULL, commodity_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_AC8B97BDB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_notification_deal_offer (id INT NOT NULL, commodity_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, INDEX IDX_9120E7FBB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_notification_offer_review (id INT NOT NULL, parent_notification_id INT NOT NULL, UNIQUE INDEX UNIQ_BF33844A78D69978 (parent_notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_user_commodity_favorite (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, commodity_id INT NOT NULL, INDEX IDX_BD462B2FA76ED395 (user_id), INDEX IDX_BD462B2FB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_category ADD CONSTRAINT FK_EBFD0C09727ACA70 FOREIGN KEY (parent_id) REFERENCES market_category (id)');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCB12469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCBB6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD CONSTRAINT FK_75DE4682B6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id)');
        $this->addSql('ALTER TABLE market_category_attribute_list_value ADD CONSTRAINT FK_75DE468212469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id)');
        $this->addSql('ALTER TABLE market_commodity ADD CONSTRAINT FK_CBDFD4E312469DE2 FOREIGN KEY (category_id) REFERENCES market_category (id)');
        $this->addSql('ALTER TABLE market_commodity_attribute_value ADD CONSTRAINT FK_8D406293B4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE market_commodity_attribute_value ADD CONSTRAINT FK_8D406293B6E62EFA FOREIGN KEY (attribute_id) REFERENCES market_category_attribute (id)');
        $this->addSql('ALTER TABLE market_commodity_kit ADD CONSTRAINT FK_51A33030BF396750 FOREIGN KEY (id) REFERENCES market_commodity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commodity_kit_commodity ADD CONSTRAINT FK_F1E8C8144FC4C805 FOREIGN KEY (commodity_kit_id) REFERENCES market_commodity_kit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commodity_kit_commodity ADD CONSTRAINT FK_F1E8C814B4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_commodity_product ADD CONSTRAINT FK_887C9734BF396750 FOREIGN KEY (id) REFERENCES market_commodity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_commodity_service ADD CONSTRAINT FK_BAAB094BBF396750 FOREIGN KEY (id) REFERENCES market_commodity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_kit_agreement ADD CONSTRAINT FK_CD3DAE93A8E60EF FOREIGN KEY (kit_id) REFERENCES market_commodity_kit (id)');
        $this->addSql('ALTER TABLE market_kit_agreement ADD CONSTRAINT FK_CD3DAE9B4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE market_notification ADD CONSTRAINT FK_3525D084F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_notification ADD CONSTRAINT FK_3525D084CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_notification_bit_offer ADD CONSTRAINT FK_AC8B97BDB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE market_notification_bit_offer ADD CONSTRAINT FK_AC8B97BDBF396750 FOREIGN KEY (id) REFERENCES market_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_notification_deal_offer ADD CONSTRAINT FK_9120E7FBB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE market_notification_deal_offer ADD CONSTRAINT FK_9120E7FBBF396750 FOREIGN KEY (id) REFERENCES market_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_notification_offer_review ADD CONSTRAINT FK_BF33844A78D69978 FOREIGN KEY (parent_notification_id) REFERENCES market_notification (id)');
        $this->addSql('ALTER TABLE market_notification_offer_review ADD CONSTRAINT FK_BF33844ABF396750 FOREIGN KEY (id) REFERENCES market_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_user_commodity_favorite ADD CONSTRAINT FK_BD462B2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_user_commodity_favorite ADD CONSTRAINT FK_BD462B2FB4ACC212 FOREIGN KEY (commodity_id) REFERENCES market_commodity (id)');
        $this->addSql('ALTER TABLE user CHANGE gender gender TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_category DROP FOREIGN KEY FK_EBFD0C09727ACA70');
        $this->addSql('ALTER TABLE category_attribute DROP FOREIGN KEY FK_3D1A3DCB12469DE2');
        $this->addSql('ALTER TABLE market_category_attribute_list_value DROP FOREIGN KEY FK_75DE468212469DE2');
        $this->addSql('ALTER TABLE market_commodity DROP FOREIGN KEY FK_CBDFD4E312469DE2');
        $this->addSql('ALTER TABLE category_attribute DROP FOREIGN KEY FK_3D1A3DCBB6E62EFA');
        $this->addSql('ALTER TABLE market_category_attribute_list_value DROP FOREIGN KEY FK_75DE4682B6E62EFA');
        $this->addSql('ALTER TABLE market_commodity_attribute_value DROP FOREIGN KEY FK_8D406293B6E62EFA');
        $this->addSql('ALTER TABLE market_commodity_attribute_value DROP FOREIGN KEY FK_8D406293B4ACC212');
        $this->addSql('ALTER TABLE market_commodity_kit DROP FOREIGN KEY FK_51A33030BF396750');
        $this->addSql('ALTER TABLE commodity_kit_commodity DROP FOREIGN KEY FK_F1E8C814B4ACC212');
        $this->addSql('ALTER TABLE market_commodity_product DROP FOREIGN KEY FK_887C9734BF396750');
        $this->addSql('ALTER TABLE market_commodity_service DROP FOREIGN KEY FK_BAAB094BBF396750');
        $this->addSql('ALTER TABLE market_kit_agreement DROP FOREIGN KEY FK_CD3DAE9B4ACC212');
        $this->addSql('ALTER TABLE market_notification_bit_offer DROP FOREIGN KEY FK_AC8B97BDB4ACC212');
        $this->addSql('ALTER TABLE market_notification_deal_offer DROP FOREIGN KEY FK_9120E7FBB4ACC212');
        $this->addSql('ALTER TABLE market_user_commodity_favorite DROP FOREIGN KEY FK_BD462B2FB4ACC212');
        $this->addSql('ALTER TABLE commodity_kit_commodity DROP FOREIGN KEY FK_F1E8C8144FC4C805');
        $this->addSql('ALTER TABLE market_kit_agreement DROP FOREIGN KEY FK_CD3DAE93A8E60EF');
        $this->addSql('ALTER TABLE market_notification_bit_offer DROP FOREIGN KEY FK_AC8B97BDBF396750');
        $this->addSql('ALTER TABLE market_notification_deal_offer DROP FOREIGN KEY FK_9120E7FBBF396750');
        $this->addSql('ALTER TABLE market_notification_offer_review DROP FOREIGN KEY FK_BF33844A78D69978');
        $this->addSql('ALTER TABLE market_notification_offer_review DROP FOREIGN KEY FK_BF33844ABF396750');
        $this->addSql('DROP TABLE market_category');
        $this->addSql('DROP TABLE category_attribute');
        $this->addSql('DROP TABLE market_category_attribute');
        $this->addSql('DROP TABLE market_category_attribute_list_value');
        $this->addSql('DROP TABLE market_commodity');
        $this->addSql('DROP TABLE market_commodity_attribute_value');
        $this->addSql('DROP TABLE market_commodity_kit');
        $this->addSql('DROP TABLE commodity_kit_commodity');
        $this->addSql('DROP TABLE market_commodity_product');
        $this->addSql('DROP TABLE market_commodity_service');
        $this->addSql('DROP TABLE market_kit_agreement');
        $this->addSql('DROP TABLE market_notification');
        $this->addSql('DROP TABLE market_notification_bit_offer');
        $this->addSql('DROP TABLE market_notification_deal_offer');
        $this->addSql('DROP TABLE market_notification_offer_review');
        $this->addSql('DROP TABLE market_user_commodity_favorite');
        $this->addSql('ALTER TABLE user CHANGE gender gender INT DEFAULT NULL');
    }
}
