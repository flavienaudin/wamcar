<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191030163001 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE prouser_proservice (id INT AUTO_INCREMENT NOT NULL, pro_user_id INT NOT NULL, pro_service_id INT NOT NULL, is_speciality TINYINT(1) NOT NULL, INDEX IDX_6748DB0152C7154E (pro_user_id), INDEX IDX_6748DB015E2A1846 (pro_service_id), UNIQUE INDEX proUserProService (pro_user_id, pro_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pro_service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) NOT NULL, slug VARCHAR(191) NOT NULL, UNIQUE INDEX UNIQ_14345C095E237E06 (name), UNIQUE INDEX UNIQ_14345C09989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prouser_proservice ADD CONSTRAINT FK_6748DB0152C7154E FOREIGN KEY (pro_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE prouser_proservice ADD CONSTRAINT FK_6748DB015E2A1846 FOREIGN KEY (pro_service_id) REFERENCES pro_service (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE prouser_proservice DROP FOREIGN KEY FK_6748DB015E2A1846');
        $this->addSql('ALTER TABLE prouser_proservice DROP FOREIGN KEY FK_6748DB0152C7154E');
        $this->addSql('DROP TABLE prouser_proservice');
        $this->addSql('DROP TABLE pro_service');
    }
}
