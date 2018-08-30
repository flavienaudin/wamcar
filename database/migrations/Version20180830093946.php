<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830093946 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_preferences (user_id INT NOT NULL, private_message_email_enabled TINYINT(1) NOT NULL, private_message_email_frequency VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\NotificationFrequency)\', like_email_enabled TINYINT(1) NOT NULL, like_email_frequency VARCHAR(255) NOT NULL COMMENT \'(DC2Type:Wamcar\\\\Vehicle\\\\Enum\\\\NotificationFrequency)\', PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_preferences ADD CONSTRAINT FK_402A6F60A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_preferences');
    }
}
