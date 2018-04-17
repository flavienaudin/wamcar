<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180417101530 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_like_vehicle (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, vehicle_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', discriminator VARCHAR(255) NOT NULL, value SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C8C1D2A7A76ED395 (user_id), INDEX IDX_C8C1D2A7545317D1 (vehicle_id), PRIMARY KEY(id), UNIQUE KEY `UNIQ_8DE6D3185E237E06F92F3E69` (`user_id`,`vehicle_id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_like_vehicle ADD CONSTRAINT FK_C8C1D2A7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_like_vehicle');
    }
}
