<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180108125802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('alter table project_vehicle drop foreign key FK_8D9EE8E3166D1F9C');
        $this->addSql('ALTER TABLE project_vehicle DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE project_vehicle CHANGE project_id project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_vehicle ADD id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_8D9EE8E3166D1F9C ON project_vehicle (project_id)');
        $this->addSql('ALTER TABLE project_vehicle ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE project_vehicle MODIFY id INT auto_increment NOT NULL');
        $this->addSql('ALTER TABLE project_vehicle ADD CONSTRAINT FK_8D9EE8E3166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_vehicle MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX IDX_8D9EE8E3166D1F9C ON project_vehicle');
        $this->addSql('ALTER TABLE project_vehicle DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE project_vehicle DROP id, CHANGE project_id project_id INT NOT NULL');
        $this->addSql('ALTER TABLE project_vehicle ADD PRIMARY KEY (project_id)');
        $this->addSql('ALTER TABLE project_vehicle DROP FOREIGN KEY FK_8D9EE8E3166D1F9C');
    }
}
