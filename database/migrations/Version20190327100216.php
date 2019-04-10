<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190327100216 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle CHANGE is_used_slug_value is_used_slug_value VARCHAR(255) NOT NULL, CHANGE slug slug VARCHAR(512) NOT NULL');
        $this->addSql('ALTER TABLE pro_vehicle CHANGE is_used_slug_value is_used_slug_value VARCHAR(255) NOT NULL, CHANGE slug slug VARCHAR(512) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE personal_vehicle CHANGE is_used_slug_value is_used_slug_value VARCHAR(255) DEFAULT NULL, CHANGE slug slug VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE pro_vehicle CHANGE is_used_slug_value is_used_slug_value VARCHAR(255) DEFAULT NULL, CHANGE slug slug VARCHAR(512) DEFAULT NULL');
    }
}
