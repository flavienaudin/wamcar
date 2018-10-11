<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181010142611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle ADD seller_id INT DEFAULT NULL');
        $this->addSql('UPDATE pro_vehicle SET seller_id = (
                                SELECT gu.pro_user_id as user_id
                                from garage_pro_user gu
                                where gu.role = \'GARAGE.ADMINISTRATOR\' and gu.garage_id = pro_vehicle.garage_id)'
        );
        $this->addSql('ALTER TABLE pro_vehicle ADD CONSTRAINT FK_EE29225D8DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EE29225D8DE820D9 ON pro_vehicle (seller_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle DROP FOREIGN KEY FK_EE29225D8DE820D9');
        $this->addSql('DROP INDEX IDX_EE29225D8DE820D9 ON pro_vehicle');
        $this->addSql('ALTER TABLE pro_vehicle DROP seller_id');
    }
}
