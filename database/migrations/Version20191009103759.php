<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191009103759 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_banner (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', file_name VARCHAR(255) NOT NULL, file_size INT NOT NULL, file_mime_type VARCHAR(255) NOT NULL, file_original_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD banner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649684EC833 FOREIGN KEY (banner_id) REFERENCES user_banner (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649684EC833 ON user (banner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649684EC833');
        $this->addSql('DROP INDEX IDX_8D93D649684EC833 ON user');
        $this->addSql('ALTER TABLE user DROP banner_id');
        $this->addSql('DROP TABLE user_banner');
    }
}
