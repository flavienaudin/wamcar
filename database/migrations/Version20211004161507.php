<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211004161507 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE videoproject_banner (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', file_name VARCHAR(255) NOT NULL, file_size INT NOT NULL, file_mime_type VARCHAR(255) NOT NULL, file_original_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_project ADD banner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE video_project ADD CONSTRAINT FK_CA5E3943684EC833 FOREIGN KEY (banner_id) REFERENCES videoproject_banner (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CA5E3943684EC833 ON video_project (banner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_project DROP FOREIGN KEY FK_CA5E3943684EC833');
        $this->addSql('DROP TABLE videoproject_banner');
        $this->addSql('DROP INDEX UNIQ_CA5E3943684EC833 ON video_project');
        $this->addSql('ALTER TABLE video_project DROP banner_id');
    }
}
