<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add vehicle field in conversation message
 */
class Version20180212153226 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75E725A671');
        $this->addSql('ALTER TABLE conversation_message CHANGE COLUMN pro_vehicle_id pro_vehicle_header_id CHAR(36) COLLATE \'utf8mb4_unicode_ci\' NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E75E725A671 FOREIGN KEY (pro_vehicle_header_id) REFERENCES pro_vehicle (id)');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E751483B071');
        $this->addSql('ALTER TABLE conversation_message CHANGE COLUMN personal_vehicle_id personal_vehicle_header_id CHAR(36) COLLATE \'utf8mb4_unicode_ci\' NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E751483B071 FOREIGN KEY (personal_vehicle_header_id) REFERENCES personal_vehicle (id)');

        $this->addSql('ALTER TABLE conversation_message ADD pro_vehicle_id CHAR(36) DEFAULT NULL, ADD personal_vehicle_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E75362EBD2F FOREIGN KEY (pro_vehicle_header_id) REFERENCES pro_vehicle (id)');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E7588158FD6 FOREIGN KEY (personal_vehicle_header_id) REFERENCES personal_vehicle (id)');
        $this->addSql('CREATE INDEX IDX_2DEB3E75362EBD2F ON conversation_message (pro_vehicle_header_id)');
        $this->addSql('CREATE INDEX IDX_2DEB3E7588158FD6 ON conversation_message (personal_vehicle_header_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75362EBD2F');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E7588158FD6');
        $this->addSql('DROP INDEX IDX_2DEB3E75362EBD2F ON conversation_message');
        $this->addSql('DROP INDEX IDX_2DEB3E7588158FD6 ON conversation_message');
        $this->addSql('ALTER TABLE conversation_message DROP pro_vehicle_header_id, DROP personal_vehicle_header_id, CHANGE conversation_id conversation_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE pro_vehicle_id pro_vehicle_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE personal_vehicle_id personal_vehicle_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
