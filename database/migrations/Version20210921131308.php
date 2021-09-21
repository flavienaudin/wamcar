<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210921131308 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE script_section (id INT AUTO_INCREMENT NOT NULL, script_section_type_id INT DEFAULT NULL, script_version_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', position INT NOT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_707BBDA58A3CB7AB (script_section_type_id), INDEX IDX_707BBDA5BCD1E6F5 (script_version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE script_version (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', video_project_iteration_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E21414898D8118F2 (video_project_iteration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE script_section_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, dialogue_label VARCHAR(255) DEFAULT NULL, dialogue_placeholder LONGTEXT DEFAULT NULL, scene_label VARCHAR(255) DEFAULT NULL, scene_placeholder LONGTEXT DEFAULT NULL, shot_label VARCHAR(255) DEFAULT NULL, instruction LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE script_sequence (id INT AUTO_INCREMENT NOT NULL, script_section_id INT DEFAULT NULL, script_shot_type_id INT DEFAULT NULL, position INT NOT NULL, dialogue LONGTEXT DEFAULT NULL, scene LONGTEXT DEFAULT NULL, INDEX IDX_C4D277622F4E228A (script_section_id), INDEX IDX_C4D27762634C7943 (script_shot_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE script_shot_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE script_section ADD CONSTRAINT FK_707BBDA58A3CB7AB FOREIGN KEY (script_section_type_id) REFERENCES script_section_type (id)');
        $this->addSql('ALTER TABLE script_section ADD CONSTRAINT FK_707BBDA5BCD1E6F5 FOREIGN KEY (script_version_id) REFERENCES script_version (id)');
        $this->addSql('ALTER TABLE script_version ADD CONSTRAINT FK_E21414898D8118F2 FOREIGN KEY (video_project_iteration_id) REFERENCES video_project_iteration (id)');
        $this->addSql('ALTER TABLE script_sequence ADD CONSTRAINT FK_C4D277622F4E228A FOREIGN KEY (script_section_id) REFERENCES script_section (id)');
        $this->addSql('ALTER TABLE script_sequence ADD CONSTRAINT FK_C4D27762634C7943 FOREIGN KEY (script_shot_type_id) REFERENCES script_shot_type (id)');

        $this->addSql('INSERT INTO script_section_type (name) VALUES ("Accroche"), ("Introduction"), ("Contenu"), ("Outroduction"), ("Appels à l\'action");');
        $this->addSql('INSERT INTO script_shot_type (label) VALUES ("Plan d\'ensemble"),("Plan pied"),("Plan américain"),("Plan taille"),("Plan poitrine"),("Gros plan"),("Très gros plan"),("Plongé"),("Contre plongé"),("Champ"),("Contre champ");');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE script_sequence DROP FOREIGN KEY FK_C4D277622F4E228A');
        $this->addSql('ALTER TABLE script_section DROP FOREIGN KEY FK_707BBDA5BCD1E6F5');
        $this->addSql('ALTER TABLE script_section DROP FOREIGN KEY FK_707BBDA58A3CB7AB');
        $this->addSql('ALTER TABLE script_sequence DROP FOREIGN KEY FK_C4D27762634C7943');
        $this->addSql('DROP TABLE script_section');
        $this->addSql('DROP TABLE script_version');
        $this->addSql('DROP TABLE script_section_type');
        $this->addSql('DROP TABLE script_sequence');
        $this->addSql('DROP TABLE script_shot_type');
    }
}
