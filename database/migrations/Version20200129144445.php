<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200129144445 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE procontact_message ADD vehicle_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE procontact_message ADD CONSTRAINT FK_5211FD15545317D1 FOREIGN KEY (vehicle_id) REFERENCES pro_vehicle (id)');
        $this->addSql('CREATE INDEX IDX_5211FD15545317D1 ON procontact_message (vehicle_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE procontact_message DROP FOREIGN KEY FK_5211FD15545317D1');
        $this->addSql('DROP INDEX IDX_5211FD15545317D1 ON procontact_message');
        $this->addSql('ALTER TABLE procontact_message DROP vehicle_id');
    }
}
