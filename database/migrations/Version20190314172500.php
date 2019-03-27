<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190314172500 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('update pro_vehicle set transmission = "TRANSMISSION_MANUAL" where transmission = "manual"');
        $this->addSql('update pro_vehicle set transmission = "TRANSMISSION_AUTOMATIC" where transmission = "auto"');
        $this->addSql('update personal_vehicle set transmission = "TRANSMISSION_MANUAL" where transmission = "manual"');
        $this->addSql('update personal_vehicle set transmission = "TRANSMISSION_AUTOMATIC" where transmission = "auto"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('update pro_vehicle set transmission = "manual" where transmission = "TRANSMISSION_MANUAL"');
        $this->addSql('update pro_vehicle set transmission = "auto" where transmission = "TRANSMISSION_AUTOMATIC"');
        $this->addSql('update personal_vehicle set transmission = "manual" where transmission = "TRANSMISSION_MANUAL"');
        $this->addSql('update personal_vehicle set transmission = "auto" where transmission = "TRANSMISSION_AUTOMATIC"');
    }
}
