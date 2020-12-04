<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201027113037 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD published_at DATETIME DEFAULT NULL, ADD unpublished_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE user u SET published_at = now() WHERE
                                EXISTS( SELECT 1 FROM user_picture up WHERE up.id = u.avatar_id)
                            AND EXISTS( SELECT 1 FROM garage_pro_user gpu WHERE gpu.pro_user_id = u.id)
                            AND EXISTS( SELECT 1 FROM prouser_proservice ps WHERE ps.pro_user_id = u.id HAVING COUNT(id) > 2)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP published_at, DROP unpublished_at');
    }
}
