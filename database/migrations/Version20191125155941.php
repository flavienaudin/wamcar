<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191125155941 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pro_service_category (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(191) NOT NULL, choice_multiple TINYINT(1) NOT NULL, position_main_filter INT DEFAULT NULL, position_more_filter INT DEFAULT NULL, UNIQUE INDEX UNIQ_42CDCDBFEA750E8 (`label`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT INTO pro_service_category (id, label) VALUES (1, \'sans\')');
        $this->addSql('ALTER TABLE pro_service ADD category_id INT NOT NULL');
        $this->addSql('UPDATE pro_service SET category_id = 1');
        $this->addSql('ALTER TABLE pro_service ADD CONSTRAINT FK_14345C0912469DE2 FOREIGN KEY (category_id) REFERENCES pro_service_category (id)');
        $this->addSql('CREATE INDEX IDX_14345C0912469DE2 ON pro_service (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_service DROP FOREIGN KEY FK_14345C0912469DE2');
        $this->addSql('DROP TABLE pro_service_category');
        $this->addSql('DROP INDEX IDX_14345C0912469DE2 ON pro_service');
        $this->addSql('ALTER TABLE pro_service DROP category_id');
    }
}
