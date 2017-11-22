<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add city on vehicle
 */
class Version20171121143350 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle ADD city_postal_code VARCHAR(255) DEFAULT NULL, ADD city_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personal_vehicle ADD city_postal_code VARCHAR(255) DEFAULT NULL, ADD city_name VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle DROP city_postal_code, DROP city_name');
        $this->addSql('ALTER TABLE pro_vehicle DROP city_postal_code, DROP city_name');}
}
