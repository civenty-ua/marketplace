<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210920092244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EF88823A92');
        $this->addSql('ALTER TABLE market_user_property DROP FOREIGN KEY FK_FA91E5EFB08FA272');
        $this->addSql('DROP INDEX IDX_FA91E5EF88823A92 ON market_user_property');
        $this->addSql('DROP INDEX IDX_FA91E5EFB08FA272 ON market_user_property');
        $this->addSql('ALTER TABLE market_user_property DROP district_id, DROP locality_id');
        $this->addSql('ALTER TABLE user ADD district_id INT DEFAULT NULL, ADD locality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64988823A92 FOREIGN KEY (locality_id) REFERENCES locality (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649B08FA272 ON user (district_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64988823A92 ON user (locality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_user_property ADD district_id INT DEFAULT NULL, ADD locality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EF88823A92 FOREIGN KEY (locality_id) REFERENCES locality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE market_user_property ADD CONSTRAINT FK_FA91E5EFB08FA272 FOREIGN KEY (district_id) REFERENCES district (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FA91E5EF88823A92 ON market_user_property (locality_id)');
        $this->addSql('CREATE INDEX IDX_FA91E5EFB08FA272 ON market_user_property (district_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B08FA272');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64988823A92');
        $this->addSql('DROP INDEX IDX_8D93D649B08FA272 ON user');
        $this->addSql('DROP INDEX IDX_8D93D64988823A92 ON user');
        $this->addSql('ALTER TABLE user DROP district_id, DROP locality_id');
    }
}
