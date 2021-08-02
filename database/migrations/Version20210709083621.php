<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210709083621 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE video_version (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', video_project_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, youtube_video_url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_5AF13A6E96CA072A (video_project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, slug VARCHAR(512) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_project_viewer (pro_user_id INT NOT NULL, video_project_id INT NOT NULL, deleted_at DATETIME DEFAULT NULL, is_creator TINYINT(1) NOT NULL, visited_at DATETIME DEFAULT NULL, INDEX IDX_322A0D9F52C7154E (pro_user_id), INDEX IDX_322A0D9F96CA072A (video_project_id), PRIMARY KEY(pro_user_id, video_project_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_project_message (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, video_project_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_CD3D6D70F675F31B (author_id), INDEX IDX_CD3D6D7096CA072A (video_project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_version ADD CONSTRAINT FK_5AF13A6E96CA072A FOREIGN KEY (video_project_id) REFERENCES video_project (id)');
        $this->addSql('ALTER TABLE video_project_viewer ADD CONSTRAINT FK_322A0D9F52C7154E FOREIGN KEY (pro_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE video_project_viewer ADD CONSTRAINT FK_322A0D9F96CA072A FOREIGN KEY (video_project_id) REFERENCES video_project (id)');
        $this->addSql('ALTER TABLE video_project_message ADD CONSTRAINT FK_CD3D6D70F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE video_project_message ADD CONSTRAINT FK_CD3D6D7096CA072A FOREIGN KEY (video_project_id) REFERENCES video_project (id)');
        $this->addSql('ALTER TABLE user ADD video_module_access TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE user SET video_module_access = FALSE where discriminator = "pro"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video_version DROP FOREIGN KEY FK_5AF13A6E96CA072A');
        $this->addSql('ALTER TABLE video_project_viewer DROP FOREIGN KEY FK_322A0D9F96CA072A');
        $this->addSql('ALTER TABLE video_project_message DROP FOREIGN KEY FK_CD3D6D7096CA072A');
        $this->addSql('DROP TABLE video_version');
        $this->addSql('DROP TABLE video_project');
        $this->addSql('DROP TABLE video_project_viewer');
        $this->addSql('DROP TABLE video_project_message');
        $this->addSql('ALTER TABLE user DROP video_module_access');
    }
}
