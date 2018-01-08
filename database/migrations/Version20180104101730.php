<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add relation PersonlUser <-> PersonalVehicle
 */
class Version20180104101730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personal_vehicle ADD CONSTRAINT FK_98A7EDADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_98A7EDADA76ED395 ON personal_vehicle (user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle DROP FOREIGN KEY FK_98A7EDADA76ED395');
        $this->addSql('DROP INDEX IDX_98A7EDADA76ED395 ON personal_vehicle');
        $this->addSql('ALTER TABLE personal_vehicle DROP user_id');
    }
}
