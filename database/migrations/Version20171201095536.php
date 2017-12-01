<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create different class for Personal and Pro Vehicle Pictures
 */
class Version20171201095536 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle_picture DROP FOREIGN KEY FK_9A812E6545317D1');
        $this->addSql('ALTER TABLE vehicle_picture ADD discriminator VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle_picture DROP discriminator');
        $this->addSql('ALTER TABLE vehicle_picture ADD CONSTRAINT FK_9A812E6545317D1 FOREIGN KEY (vehicle_id) REFERENCES personal_vehicle (id)');
    }
}
