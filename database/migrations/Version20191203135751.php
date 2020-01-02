<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203135751 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE users_hobbies (user_id INT NOT NULL, hobby_id INT NOT NULL, INDEX IDX_C8CA40D7A76ED395 (user_id), INDEX IDX_C8CA40D7322B2123 (hobby_id), PRIMARY KEY(user_id, hobby_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hobby (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) NOT NULL, slug  VARCHAR(191) NOT NULL, icon VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3964F3375E237E06 (name), UNIQUE INDEX UNIQ_3964F337989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users_hobbies ADD CONSTRAINT FK_C8CA40D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE users_hobbies ADD CONSTRAINT FK_C8CA40D7322B2123 FOREIGN KEY (hobby_id) REFERENCES hobby (id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users_hobbies DROP FOREIGN KEY FK_C8CA40D7322B2123');
        $this->addSql('DROP TABLE users_hobbies');
        $this->addSql('DROP TABLE hobby');
    }
}
