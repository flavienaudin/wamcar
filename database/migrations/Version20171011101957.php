<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add registration feature
 */
class Version20171011101957 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(191) NOT NULL, name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATE NOT NULL, title_value VARCHAR(255) DEFAULT NULL, city_postal_code VARCHAR(255) DEFAULT NULL, city_city VARCHAR(255) DEFAULT NULL, discriminator VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, salt VARCHAR(255) DEFAULT NULL, last_login_ip VARCHAR(45) DEFAULT NULL, registration_ip VARCHAR(45) DEFAULT NULL, newsletter_optin TINYINT(1) DEFAULT NULL, registration_token VARCHAR(32) DEFAULT NULL, password_reset_token VARCHAR(32) DEFAULT NULL, slug VARCHAR(128) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649D09D01D3 (registration_token), UNIQUE INDEX UNIQ_8D93D6496B7BA4B6 (password_reset_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user');
    }
}
