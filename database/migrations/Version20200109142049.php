<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109142049 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE user SET video_text = concat(video_short_text, video_text) WHERE video_short_text IS NOT NULL OR video_text IS NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE video_short_text video_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE user SET video_title = NULL ');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE video_title video_short_text LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE user SET video_short_text = video_text');

    }
}
