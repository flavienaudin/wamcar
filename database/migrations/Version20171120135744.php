<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add pro vehicle
 */
class Version20171120135744 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pro_vehicle (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', transmission VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', safety_test_state VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\SafetyTestState)\', safety_test_date VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\SafetyTestDate)\', maintenance_state VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\MaintenanceState)\', registration_date DATE NOT NULL, mileage INT NOT NULL, body_state INT NOT NULL, engine_state INT DEFAULT NULL, tyre_state INT DEFAULT NULL, is_timing_belt_changed TINYINT(1) DEFAULT NULL, is_imported TINYINT(1) DEFAULT NULL, is_first_hand TINYINT(1) DEFAULT NULL, additional_information LONGTEXT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, catalog_price DOUBLE PRECISION DEFAULT NULL, discount DOUBLE PRECISION DEFAULT NULL, guarantee VARCHAR(255) DEFAULT NULL, refunded TINYINT(1) DEFAULT \'0\' NOT NULL, other_guarantee VARCHAR(255) DEFAULT NULL, additional_services LONGTEXT DEFAULT NULL, `reference` VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, registration_mine_type VARCHAR(255) DEFAULT NULL, registration_plate_number VARCHAR(255) DEFAULT NULL, model_version_name VARCHAR(255) NOT NULL, model_version_engine_name VARCHAR(255) NOT NULL, model_version_engine_fuel_name VARCHAR(255) NOT NULL, model_version_model_name VARCHAR(255) NOT NULL, model_version_model_make_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pro_vehicle');
    }
}
