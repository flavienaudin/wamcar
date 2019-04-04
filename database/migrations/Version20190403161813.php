<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403161813 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lead (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, pro_user_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, last_contacted_at DATETIME NOT NULL, nb_phone_action INT NOT NULL, nb_phone_pro_action INT NOT NULL, nb_messages INT NOT NULL, nb_likes INT NOT NULL, UNIQUE INDEX UNIQ_289161CBA76ED395 (user_id), INDEX IDX_289161CB52C7154E (pro_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead ADD CONSTRAINT FK_289161CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE lead ADD CONSTRAINT FK_289161CB52C7154E FOREIGN KEY (pro_user_id) REFERENCES user (id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lead');
    }
}
