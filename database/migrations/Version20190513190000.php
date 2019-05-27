<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190513190000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Diesel" where model_version_engine_fuel_name = "Gasoil"');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Diesel" where model_version_engine_fuel_name = "GO" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Electrique" where model_version_engine_fuel_name = "Courant électrique" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Essence" where model_version_engine_fuel_name = "Energie Essence" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Essence" where model_version_engine_fuel_name = "ES" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Hybride" where model_version_engine_fuel_name = "HYBRID" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Hybride" where model_version_engine_fuel_name = "Hybride Diesel Electrique" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Hybride" where model_version_engine_fuel_name = "Hybride Diesel Electrique" ');
        $this->addSql('UPDATE pro_vehicle set  model_version_engine_fuel_name = "Hybride" where model_version_engine_fuel_name = "Hybride Diesel / Courant Electrique" ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
