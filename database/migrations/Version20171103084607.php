<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add garage pro user
 */
class Version20171103084607 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE garage_pro_user (garage_id INT NOT NULL, pro_user_id INT NOT NULL, INDEX IDX_AD3AB093C4FFF555 (garage_id), INDEX IDX_AD3AB09352C7154E (pro_user_id), PRIMARY KEY(garage_id, pro_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE garage_pro_user ADD CONSTRAINT FK_AD3AB093C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id)');
        $this->addSql('ALTER TABLE garage_pro_user ADD CONSTRAINT FK_AD3AB09352C7154E FOREIGN KEY (pro_user_id) REFERENCES user (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE garage_pro_user');

    }
}
