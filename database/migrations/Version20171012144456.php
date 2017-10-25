<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Feature: Picture upload
 * Change: Create personal_vehicle and vehicle_picture tables
 */
class Version20171012144456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE personal_vehicle (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_picture (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', vehicle_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', caption VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, file_size INT NOT NULL, file_mime_type VARCHAR(255) NOT NULL, file_original_name VARCHAR(255) NOT NULL, INDEX IDX_9A812E6545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vehicle_picture ADD CONSTRAINT FK_9A812E6545317D1 FOREIGN KEY (vehicle_id) REFERENCES personal_vehicle (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle_picture DROP FOREIGN KEY FK_9A812E6545317D1');
        $this->addSql('DROP TABLE personal_vehicle');
        $this->addSql('DROP TABLE vehicle_picture');
    }
}
