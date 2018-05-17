<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180517155443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle CHANGE safety_test_date safety_test_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE pro_vehicle CHANGE safety_test_date safety_test_date DATE DEFAULT NULL');

        $this->addSql('UPDATE personal_vehicle SET safety_test_date = NULL');
        $this->addSql('UPDATE pro_vehicle SET safety_test_date = NULL');

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle CHANGE safety_test_date safety_test_date VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE pro_vehicle CHANGE safety_test_date safety_test_date VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('UPDATE personal_vehicle SET safety_test_date = NULL');
        $this->addSql('UPDATE pro_vehicle SET safety_test_date = NULL');
    }
}
