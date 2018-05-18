<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180518160000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE personal_vehicle SET safety_test_state = null, body_state = null, engine_state = null, tyre_state = null, maintenance_state = null, timing_belt_state = null, is_imported = null, is_first_hand = null WHERE is_used = false');
        $this->addSql('UPDATE pro_vehicle  SET safety_test_state = null, body_state = null, engine_state = null, tyre_state = null, maintenance_state = null, timing_belt_state = null, is_imported = null, is_first_hand = null WHERE is_used = false');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
