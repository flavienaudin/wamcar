<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190708125733 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_preferences 
                                ADD lead_email_enabled TINYINT(1) NOT NULL, 
                                ADD lead_localization_radius_criteria INT NOT NULL, 
                                ADD lead_part_exchange_selection_criteria VARCHAR(255) NOT NULL, 
                                ADD lead_part_exchange_km_max_criteria INT DEFAULT NULL, 
                                ADD lead_project_selection_criteria VARCHAR(255) NOT NULL, 
                                ADD lead_project_budget_min_criteria INT DEFAULT NULL');
        $this->addSql('UPDATE user_preferences 
                                SET lead_email_enabled = 1, 
                                    lead_localization_radius_criteria = 50, 
                                    lead_part_exchange_selection_criteria = "leadcriteria.no_matter", 
                                    lead_project_selection_criteria = "leadcriteria.no_matter"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_preferences DROP lead_email_enabled, DROP lead_localization_radius_criteria, DROP lead_part_exchange_selection_criteria, DROP lead_part_exchange_km_max_criteria, DROP lead_project_selection_criteria, DROP lead_project_budget_min_criteria');
    }
}
