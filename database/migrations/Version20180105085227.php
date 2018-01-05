<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add project vehicle
 */
class Version20180105085227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project_vehicle (project_id INT NOT NULL, make VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, year_max INT DEFAULT NULL, mileage_max INT DEFAULT NULL, PRIMARY KEY(project_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_vehicle ADD CONSTRAINT FK_8D9EE8E3166D1F9C FOREIGN KEY (project_id) REFERENCES project (personal_user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE project_vehicle');
    }
}
