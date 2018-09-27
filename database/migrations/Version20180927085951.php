<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180927085951 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE affinity_degree (pro_user_id INT NOT NULL, personal_user_id INT NOT NULL, pro_personal_score DOUBLE PRECISION NOT NULL, personal_pro_score DOUBLE PRECISION NOT NULL, INDEX IDX_B118D51152C7154E (pro_user_id), INDEX IDX_B118D5112449DABE (personal_user_id), PRIMARY KEY(pro_user_id, personal_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D51152C7154E FOREIGN KEY (pro_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE affinity_degree ADD CONSTRAINT FK_B118D5112449DABE FOREIGN KEY (personal_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE affinity_degree');
    }
}
