<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Persist vehicle specifics for PersonalVehicle
 */
class Version20171018121156 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD safety_test_state VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\SafetyTestState)\', ADD safety_test_date VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\SafetyTestDate)\', ADD mileage INT NOT NULL, DROP safety_test, CHANGE transmission transmission VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', CHANGE maintenance_state maintenance_state VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\MaintenanceState)\'');
        $this->addSql('ALTER TABLE user CHANGE title title VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:Wamcar\\\\User\\\\Title)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD safety_test VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', DROP safety_test_state, DROP safety_test_date, DROP mileage, CHANGE transmission transmission VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', CHANGE maintenance_state maintenance_state VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\'');
        $this->addSql('ALTER TABLE user CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\User\\\\Title)\'');
    }
}
