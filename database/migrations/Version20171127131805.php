<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add latitude and longitude in City
 */
class Version20171127131805 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle ADD city_latitude DOUBLE PRECISION DEFAULT NULL, ADD city_longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE personal_vehicle ADD city_latitude DOUBLE PRECISION DEFAULT NULL, ADD city_longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE garage ADD address_city_latitude DOUBLE PRECISION DEFAULT NULL, ADD address_city_longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD profile_city_latitude DOUBLE PRECISION DEFAULT NULL, ADD profile_city_longitude DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE garage DROP address_city_latitude, DROP address_city_longitude');
        $this->addSql('ALTER TABLE personal_vehicle DROP city_latitude, DROP city_longitude');
        $this->addSql('ALTER TABLE pro_vehicle DROP city_latitude, DROP city_longitude');
        $this->addSql('ALTER TABLE user DROP profile_city_latitude, DROP profile_city_longitude');
    }
}
