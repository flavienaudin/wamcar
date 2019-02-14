<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181210135235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE affinity_personal_answers (answer_id CHAR(36) NOT NULL, budget INT DEFAULT NULL, searched_advices LONGTEXT DEFAULT NULL, new_used VARCHAR(255) DEFAULT NULL, vehicle_usage VARCHAR(255) DEFAULT NULL, vehicle_number INT DEFAULT NULL, personal_company_activity VARCHAR(255) DEFAULT NULL, how_help VARCHAR(255) DEFAULT NULL, generation LONGTEXT DEFAULT NULL, vehicle_body LONGTEXT DEFAULT NULL, energy LONGTEXT DEFAULT NULL, seats_number INT DEFAULT NULL, strong_points LONGTEXT DEFAULT NULL, improvements LONGTEXT DEFAULT NULL, security_options LONGTEXT DEFAULT NULL, confort_options LONGTEXT DEFAULT NULL, options_choice LONGTEXT DEFAULT NULL, searched_hobbies LONGTEXT DEFAULT NULL, searched_title VARCHAR(255) DEFAULT NULL, searched_experience VARCHAR(255) DEFAULT NULL, uniform LONGTEXT DEFAULT NULL, first_contact_channel LONGTEXT DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, availabilities LONGTEXT DEFAULT NULL, first_contact_pref VARCHAR(255) DEFAULT NULL, other_hobbies LONGTEXT DEFAULT NULL, road VARCHAR(255) DEFAULT NULL, PRIMARY KEY(answer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE affinity_pro_answers (answer_id CHAR(36) NOT NULL, title VARCHAR(255) DEFAULT NULL, main_profession VARCHAR(255) DEFAULT NULL, experience VARCHAR(255) DEFAULT NULL, uniform VARCHAR(255) DEFAULT NULL, hobby VARCHAR(255) DEFAULT NULL, hobby_level INT DEFAULT NULL, advices LONGTEXT DEFAULT NULL, vehicle_body LONGTEXT DEFAULT NULL, brands LONGTEXT DEFAULT NULL, first_contact_channel LONGTEXT DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, availabilities LONGTEXT DEFAULT NULL, first_contact_pref VARCHAR(255) DEFAULT NULL, suggestion VARCHAR(255) DEFAULT NULL, prices LONGTEXT DEFAULT NULL, other_hobbies LONGTEXT DEFAULT NULL, road VARCHAR(255) DEFAULT NULL, PRIMARY KEY(answer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affinity_personal_answers ADD CONSTRAINT FK_5D659B08AA334807 FOREIGN KEY (answer_id) REFERENCES affinity_answer (id)');
        $this->addSql('ALTER TABLE affinity_pro_answers ADD CONSTRAINT FK_2B3E7121AA334807 FOREIGN KEY (answer_id) REFERENCES affinity_answer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE affinity_personal_answers');
        $this->addSql('DROP TABLE affinity_pro_answers');
    }
}
