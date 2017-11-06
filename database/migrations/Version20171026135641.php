<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add garage
 */
class Version20171026135641 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE garage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, siren VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(191) NOT NULL, opening_hours LONGTEXT DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, benefit LONGTEXT DEFAULT NULL, address_address VARCHAR(255) DEFAULT NULL, address_city_postal_code VARCHAR(255) DEFAULT NULL, address_city_name VARCHAR(255) DEFAULT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE garage CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE garage DROP discriminator');
        $this->addSql('ALTER TABLE garage ADD discriminator VARCHAR(255) NOT NULL, ADD deleted_at DATE DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE garage');
    }
}
