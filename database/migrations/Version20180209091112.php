<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Refacto message with relation with vehicleHeader
 */
class Version20180209091112 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversation_message ADD pro_vehicle_id CHAR(36) DEFAULT NULL, ADD personal_vehicle_id CHAR(36) DEFAULT NULL, DROP vehicle_header_id');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E75E725A671 FOREIGN KEY (pro_vehicle_id) REFERENCES pro_vehicle (id)');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E751483B071 FOREIGN KEY (personal_vehicle_id) REFERENCES personal_vehicle (id)');
        $this->addSql('CREATE INDEX IDX_2DEB3E75E725A671 ON conversation_message (pro_vehicle_id)');
        $this->addSql('CREATE INDEX IDX_2DEB3E751483B071 ON conversation_message (personal_vehicle_id)');
     }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75E725A671');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E751483B071');
        $this->addSql('DROP INDEX IDX_2DEB3E75E725A671 ON conversation_message');
        $this->addSql('DROP INDEX IDX_2DEB3E751483B071 ON conversation_message');
        $this->addSql('ALTER TABLE conversation_message ADD vehicle_header_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP pro_vehicle_id, DROP personal_vehicle_id');
    }
}
