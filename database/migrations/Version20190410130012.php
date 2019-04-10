<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190410130012 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sale_declaration (id CHAR(36) NOT NULL, pro_user_seller_id INT NOT NULL, lead_id INT DEFAULT NULL, seller_first_name VARCHAR(255) DEFAULT NULL, seller_last_name VARCHAR(255) DEFAULT NULL, buyer_first_name VARCHAR(255) DEFAULT NULL, buyer_last_name VARCHAR(255) DEFAULT NULL, transaction_amount INT DEFAULT NULL, credit_earned INT DEFAULT NULL, deleted_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EA2BCD7F2FF21C59 (pro_user_seller_id), INDEX IDX_EA2BCD7F55458D (lead_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sale_declaration ADD CONSTRAINT FK_EA2BCD7F2FF21C59 FOREIGN KEY (pro_user_seller_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale_declaration ADD CONSTRAINT FK_EA2BCD7F55458D FOREIGN KEY (lead_id) REFERENCES lead (id)');
        $this->addSql('ALTER TABLE lead ADD status VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE lead set status=\'leadStatus.to_qualify\'');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sale_declaration');
        $this->addSql('ALTER TABLE lead DROP status');
    }
}
