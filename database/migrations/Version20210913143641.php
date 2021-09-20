<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913143641 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Clean tables and data about VideoProject to add the VideoProjectIteration layer
        $this->addSql('DELETE FROM notifiable_notification WHERE notification_id in (SELECT id FROM notification WHERE subject LIKE \'%VideoCoaching%\');');
        $this->addSql('DELETE FROM notification WHERE subject LIKE \'%VideoCoaching%\';');
        $this->addSql('DELETE FROM videocoaching_vpm_attachment');
        $this->addSql('DELETE FROM video_project_message');
        $this->addSql('DELETE FROM video_project_viewer');
        $this->addSql('DELETE FROM video_version');
        $this->addSql('DELETE FROM video_project');


        $this->addSql('CREATE TABLE video_project_iteration (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', video_project_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_7C939B1B96CA072A (video_project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_project_iteration ADD CONSTRAINT FK_7C939B1B96CA072A FOREIGN KEY (video_project_id) REFERENCES video_project (id)');
        $this->addSql('ALTER TABLE video_version DROP FOREIGN KEY FK_5AF13A6E96CA072A');
        $this->addSql('DROP INDEX IDX_5AF13A6E96CA072A ON video_version');
        $this->addSql('ALTER TABLE video_version ADD video_project_iteration_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP video_project_id');
        $this->addSql('ALTER TABLE video_version ADD CONSTRAINT FK_5AF13A6E8D8118F2 FOREIGN KEY (video_project_iteration_id) REFERENCES video_project_iteration (id)');
        $this->addSql('CREATE INDEX IDX_5AF13A6E8D8118F2 ON video_version (video_project_iteration_id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Clean tables and data about VideoProject to add the VideoProjectIteration layer
        $this->addSql('DELETE FROM notifiable_notification WHERE notification_id in (SELECT id FROM notification WHERE subject LIKE \'%VideoCoaching%\');');
        $this->addSql('DELETE FROM notification WHERE subject LIKE \'%VideoCoaching%\';');
        $this->addSql('DELETE FROM videocoaching_vpm_attachment');
        $this->addSql('DELETE FROM video_project_message');
        $this->addSql('DELETE FROM video_project_viewer');
        $this->addSql('DELETE FROM video_version');
        $this->addSql('DELETE FROM video_project');
        $this->addSql('ALTER TABLE video_version DROP FOREIGN KEY FK_5AF13A6E8D8118F2');
        $this->addSql('DROP TABLE video_project_iteration');
        $this->addSql('DROP INDEX IDX_5AF13A6E8D8118F2 ON video_version');
        $this->addSql('ALTER TABLE video_version ADD video_project_id INT DEFAULT NULL, DROP video_project_iteration_id');
        $this->addSql('ALTER TABLE video_version ADD CONSTRAINT FK_5AF13A6E96CA072A FOREIGN KEY (video_project_id) REFERENCES video_project (id)');
        $this->addSql('CREATE INDEX IDX_5AF13A6E96CA072A ON video_version (video_project_id)');
    }
}
