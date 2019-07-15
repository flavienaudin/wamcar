<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190715144338 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_preferences 
                                ADD lead_only_part_exchange TINYINT(1) NOT NULL, 
                                ADD lead_only_project TINYINT(1) NOT NULL, 
                                ADD lead_project_with_part_exchange TINYINT(1) NOT NULL, 
                                DROP lead_part_exchange_selection_criteria, 
                                DROP lead_project_selection_criteria');
        $this->addSql('UPDATE user_preferences set lead_only_part_exchange = 1, lead_only_project = 1, lead_project_with_part_exchange = 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

         $this->addSql('ALTER TABLE user_preferences 
                                ADD lead_part_exchange_selection_criteria VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, 
                                ADD lead_project_selection_criteria VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, 
                                DROP lead_only_part_exchange, 
                                DROP lead_only_project, 
                                DROP lead_project_with_part_exchange');
        $this->addSql('UPDATE user_preferences 
                                SET lead_part_exchange_selection_criteria = "leadcriteria.no_matter", 
                                    lead_project_selection_criteria = "leadcriteria.no_matter"');
    }
}
