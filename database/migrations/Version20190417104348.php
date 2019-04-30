<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190417104348 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sale_declaration (id CHAR(36) NOT NULL, pro_user_seller_id INT NOT NULL, lead_customer_id INT DEFAULT NULL, seller_first_name VARCHAR(255) DEFAULT NULL, seller_last_name VARCHAR(255) DEFAULT NULL, customer_first_name VARCHAR(255) DEFAULT NULL, customer_last_name VARCHAR(255) DEFAULT NULL, transaction_sale_amount INT DEFAULT NULL, transaction_part_exchange_amount INT DEFAULT NULL, transaction_commentary LONGTEXT DEFAULT NULL, credit_earned INT DEFAULT NULL, deleted_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BEF0BAF2FF21C59 (pro_user_seller_id), INDEX IDX_BEF0BAF6A048787 (lead_customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sale_declaration ADD CONSTRAINT FK_BEF0BAF2FF21C59 FOREIGN KEY (pro_user_seller_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale_declaration ADD CONSTRAINT FK_BEF0BAF6A048787 FOREIGN KEY (lead_customer_id) REFERENCES lead (id)');

        $this->addSql('ALTER TABLE pro_vehicle ADD declaration_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE pro_vehicle ADD CONSTRAINT FK_EE29225DC06258A3 FOREIGN KEY (declaration_id) REFERENCES sale_declaration (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE29225DC06258A3 ON pro_vehicle (declaration_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pro_vehicle DROP FOREIGN KEY FK_EE29225DC06258A3');
        $this->addSql('DROP INDEX UNIQ_EE29225DC06258A3 ON pro_vehicle');
        $this->addSql('DROP TABLE sale_declaration');
        $this->addSql('ALTER TABLE pro_vehicle DROP declaration_id');
    }
}
