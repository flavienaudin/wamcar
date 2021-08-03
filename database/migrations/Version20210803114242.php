<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210803114242 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_preferences ADD video_project_sharing_email_enabled TINYINT(1) NOT NULL, ADD video_project_sharing_email_frequency VARCHAR(255) NOT NULL, ADD video_project_new_message_email_enabled TINYINT(1) NOT NULL, ADD video_project_new_message_email_frequency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE user_preferences SET video_project_sharing_email_enabled = TRUE, video_project_sharing_email_frequency = "IMMEDIATELY", video_project_new_message_email_frequency = "IMMEDIATELY", video_project_new_message_email_enabled = TRUE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_preferences DROP video_project_sharing_email_enabled, DROP video_project_sharing_email_frequency, DROP video_project_new_message_email_enabled, DROP video_project_new_message_email_frequency');
    }
}
