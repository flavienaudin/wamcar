<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180509092301 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD timing_belt_state VARCHAR(255) DEFAULT NULL, DROP is_timing_belt_changed, ADD is_used TINYINT(1) NOT NULL, CHANGE safety_test_state safety_test_state VARCHAR(255) DEFAULT NULL, CHANGE safety_test_date safety_test_date VARCHAR(255) DEFAULT NULL, CHANGE maintenance_state maintenance_state VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pro_vehicle ADD timing_belt_state VARCHAR(255) DEFAULT NULL, DROP is_timing_belt_changed, ADD is_used TINYINT(1) NOT NULL, CHANGE safety_test_state safety_test_state VARCHAR(255) DEFAULT NULL, CHANGE safety_test_date safety_test_date VARCHAR(255) DEFAULT NULL, CHANGE maintenance_state maintenance_state VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE personal_vehicle SET maintenance_state = NULL WHERE maintenance_state = ""');
        $this->addSql('UPDATE personal_vehicle SET safety_test_date = NULL WHERE safety_test_date = ""');
        $this->addSql('UPDATE personal_vehicle SET safety_test_state = NULL WHERE safety_test_state = ""');
        $this->addSql('UPDATE personal_vehicle SET is_first_hand = NULL WHERE safety_test_state = ""');
        $this->addSql('UPDATE pro_vehicle SET maintenance_state = NULL WHERE maintenance_state = ""');
        $this->addSql('UPDATE pro_vehicle SET safety_test_date = NULL WHERE safety_test_date = ""');
        $this->addSql('UPDATE pro_vehicle SET safety_test_state = NULL WHERE safety_test_state = ""');
        $this->addSql('UPDATE pro_vehicle SET is_first_hand = false WHERE is_first_hand IS NULL');


    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle ADD is_timing_belt_changed TINYINT(1) DEFAULT NULL, DROP timing_belt_state, DROP is_used, CHANGE safety_test_state safety_test_state VARCHAR(255) NOT NULL, CHANGE safety_test_date safety_test_date VARCHAR(255) NOT NULL, CHANGE maintenance_state maintenance_state VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pro_vehicle ADD is_timing_belt_changed TINYINT(1) DEFAULT NULL, DROP timing_belt_state, DROP is_used, CHANGE safety_test_state safety_test_state VARCHAR(255) NOT NULL, CHANGE safety_test_date safety_test_date VARCHAR(255) NOT NULL, CHANGE maintenance_state maintenance_state VARCHAR(255) NOT NULL');
    }
}
