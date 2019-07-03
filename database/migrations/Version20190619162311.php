<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190619162311 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE lead
            ADD initiated_by VARCHAR(255) NOT NULL,
            CHANGE nb_phone_action nb_phone_action_by_lead INT NOT NULL,
            CHANGE nb_phone_pro_action nb_phone_pro_action_by_lead INT NOT NULL,
            CHANGE nb_messages nb_lead_messages INT NOT NULL,
            CHANGE nb_likes nb_lead_likes INT NOT NULL,
            ADD nb_phone_action_by_pro INT NOT NULL, 
            ADD nb_phone_pro_action_by_pro INT NOT NULL, 
            ADD nb_pro_messages INT NOT NULL, 
            ADD nb_pro_likes INT NOT NULL,
            CHANGE status status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lead
            DROP initiated_by,
            CHANGE nb_phone_action_by_lead nb_phone_action INT NOT NULL,
            CHANGE nb_phone_pro_action_by_lead nb_phone_pro_action INT NOT NULL,
            CHANGE nb_lead_messages nb_messages INT NOT NULL,
            CHANGE nb_lead_likes nb_likes INT NOT NULL,
            DROP nb_phone_action_by_pro, 
            DROP nb_phone_pro_action_by_pro, 
            DROP nb_pro_messages, 
            DROP nb_pro_likes,
            CHANGE status status VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
