<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180927100507 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE affinity_degree (main_user_id INT NOT NULL, with_user_id INT NOT NULL, affinity_value DOUBLE PRECISION NOT NULL, INDEX IDX_B118D51153257A7C (main_user_id), INDEX IDX_B118D511AE83ED76 (with_user_id), PRIMARY KEY(main_user_id, with_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D51153257A7C FOREIGN KEY (main_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D511AE83ED76 FOREIGN KEY (with_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE affinity_degree');
    }
}
