<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211021141202 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_project_document ADD viewer_prouser_id INT NOT NULL, ADD viewer_videoproject_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_project_document ADD CONSTRAINT FK_48AD17BA3B70668165D66035 FOREIGN KEY (viewer_prouser_id, viewer_videoproject_id) REFERENCES video_project_viewer (pro_user_id, video_project_id)');
        $this->addSql('CREATE INDEX IDX_48AD17BA3B70668165D66035 ON video_project_document (viewer_prouser_id, viewer_videoproject_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_project_document DROP FOREIGN KEY FK_48AD17BA3B70668165D66035');
        $this->addSql('DROP INDEX IDX_48AD17BA3B70668165D66035 ON video_project_document');
        $this->addSql('ALTER TABLE video_project_document DROP viewer_prouser_id, DROP viewer_videoproject_id');
    }
}
