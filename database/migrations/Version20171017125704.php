<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Use enums as a doctrine type
 */
class Version20171017125704 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD transmission VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', ADD safety_test VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', ADD maintenance_state VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\'');
        $this->addSql('ALTER TABLE user ADD title VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:Wamcar\\\\User\\\\Title)\', DROP title_value');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle CHANGE transmission transmission VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', CHANGE safety_test safety_test VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\', CHANGE maintenance_state maintenance_state VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\Transmission)\'');
        $this->addSql('ALTER TABLE user ADD title_value VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP title');
    }
}
