<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Persist vehicle information for PersonalVehicle
 */
class Version20171017115626 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD registration_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', ADD body_state INT NOT NULL, ADD engine_state INT DEFAULT NULL, ADD tyre_state INT DEFAULT NULL, ADD is_timing_belt_changed TINYINT(1) DEFAULT NULL, ADD is_imported TINYINT(1) DEFAULT NULL, ADD is_first_hand TINYINT(1) DEFAULT NULL, ADD additional_information LONGTEXT DEFAULT NULL, ADD registration_mine_type VARCHAR(255) DEFAULT NULL, ADD registration_plate_number VARCHAR(255) DEFAULT NULL, ADD model_version_name VARCHAR(255) NOT NULL, ADD model_version_engine_name VARCHAR(255) NOT NULL, ADD model_version_engine_fuel_name VARCHAR(255) NOT NULL, ADD model_version_model_name VARCHAR(255) NOT NULL, ADD model_version_model_make_name VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle DROP registration_date, DROP body_state, DROP engine_state, DROP tyre_state, DROP is_timing_belt_changed, DROP is_imported, DROP is_first_hand, DROP additional_information, DROP registration_mine_type, DROP registration_plate_number, DROP model_version_name, DROP model_version_engine_name, DROP model_version_engine_fuel_name, DROP model_version_model_name, DROP model_version_model_make_name, DROP created_at');
    }
}
