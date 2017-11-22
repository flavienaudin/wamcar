<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add relation between proVehicle and Garage
 */
class Version20171121100213 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle ADD garage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pro_vehicle ADD CONSTRAINT FK_EE29225DC4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id)');
        $this->addSql('CREATE INDEX IDX_EE29225DC4FFF555 ON pro_vehicle (garage_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle DROP FOREIGN KEY FK_EE29225DC4FFF555');
        $this->addSql('DROP INDEX IDX_EE29225DC4FFF555 ON pro_vehicle');
        $this->addSql('ALTER TABLE pro_vehicle DROP garage_id');
    }
}
