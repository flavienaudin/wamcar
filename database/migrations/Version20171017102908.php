<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add User Profile
 */
class Version20171017102908 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD user_profile_name VARCHAR(255) DEFAULT NULL, ADD user_profile_phone VARCHAR(255) DEFAULT NULL, ADD user_profile_title_value VARCHAR(255) DEFAULT NULL, ADD user_profile_city_postal_code VARCHAR(255) DEFAULT NULL, ADD user_profile_city_name VARCHAR(255) DEFAULT NULL, DROP name, DROP phone, DROP title_value, DROP city_postal_code, DROP city_name');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD phone VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD title_value VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD city_postal_code VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD city_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP user_profile_name, DROP user_profile_phone, DROP user_profile_title_value, DROP user_profile_city_postal_code, DROP user_profile_city_name');
    }
}
