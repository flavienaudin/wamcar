<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * clean key conversation message
 */
class Version20180216085100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E7588158FD6');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E751483B071');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E75E725A671');
        $this->addSql('DROP INDEX IDX_2DEB3E75E725A671 ON conversation_message');
        $this->addSql('DROP INDEX IDX_2DEB3E751483B071 ON conversation_message');
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
        $this->addSql('CREATE INDEX IDX_2DEB3E75E725A671 ON conversation_message (pro_vehicle_header_id)');
        $this->addSql('CREATE INDEX IDX_2DEB3E751483B071 ON conversation_message (personal_vehicle_header_id)');
    }
}
