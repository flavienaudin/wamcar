<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add garage banner and logo
 */
class Version20180103142350 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE garage_picture (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', garage_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, file_size INT NOT NULL, file_mime_type VARCHAR(255) NOT NULL, file_original_name VARCHAR(255) NOT NULL, discriminator VARCHAR(255) NOT NULL, INDEX IDX_909BA319C4FFF555 (garage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE garage_picture ADD CONSTRAINT FK_909BA319C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE garage_picture');
    }
}
