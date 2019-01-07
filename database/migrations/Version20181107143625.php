<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181107143625 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE affinity_degree (smaller_id_user_id INT NOT NULL, greated_id_user_id INT NOT NULL, affinity_value DOUBLE PRECISION NOT NULL, profile_affinity_value DOUBLE PRECISION NOT NULL, passion_affinity_value DOUBLE PRECISION NOT NULL, positioning_affinity_value DOUBLE PRECISION NOT NULL, atomes_crochus_affinity_value DOUBLE PRECISION NOT NULL, INDEX IDX_B118D511B9F6D139 (smaller_id_user_id), INDEX IDX_B118D511D3DFA442 (greated_id_user_id), PRIMARY KEY(smaller_id_user_id, greated_id_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D511B9F6D139 FOREIGN KEY (smaller_id_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D511D3DFA442 FOREIGN KEY (greated_id_user_id) REFERENCES user (id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE affinity_degree');
    }
}
