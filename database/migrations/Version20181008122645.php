<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181008122645 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE garage_pro_user ADD role VARCHAR(255) NOT NULL, ADD requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE garage CHANGE name name VARCHAR(128) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F26610B983C031 ON garage (google_place_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F26610B5E237E06 ON garage (name)');

        $this->addSql('UPDATE garage_pro_user SET role = \'GARAGE.ADMINISTRATOR\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_9F26610B983C031 ON garage');
        $this->addSql('DROP INDEX UNIQ_9F26610B5E237E06 ON garage');
        $this->addSql('ALTER TABLE garage CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE garage_pro_user DROP role, DROP requested_at');
    }
}
