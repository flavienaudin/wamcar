<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191205101500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM users_hobbies;');
        $this->addSql('DELETE FROM hobby');

        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Astronomie', 'astronomie', 'astronomie.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Aventure', 'aventure', 'aventure.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Barbecue', 'barbecue', 'barbecue.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Basket', 'basket', 'basket.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Bateau', 'bateau', 'bateau.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Bowling', 'bowling', 'bowling.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Boxe', 'boxe', 'boxe.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Bricolage', 'bricolage', 'bricolage.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Canoë', 'canoe', 'canoe.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Chat', 'chat', 'chat.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Couture', 'couture', 'couture.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Billard', 'billard', 'billard.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Camping', 'camping', 'camping.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Chant', 'chant', 'chant.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Cinema', 'cinema', 'cinema.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Cocktail', 'cocktail', 'cocktail.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Cuisine', 'cuisine', 'cuisine.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Echec', 'echec', 'echec.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Escrime', 'escrime', 'escrime.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Fête','fete','fete.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Flechette', 'flechette', 'flechette.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Foot','foot','foot.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Golf','golf','golf.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Guitare','guitare','guitare.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Hélicoptère','helicoptere','helicoptere.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Jardinage','jardinage','jardinage.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Jeux de société','jeux-de-societe','jeux_de_societe.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Jeux vidéo','jeux-video','jeux_video.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Luxe','luxe','luxe.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Magie','magie','magie.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Mer','mer','mer.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Mode','mode','mode.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Mongolfière','mongolfiere','mongolfiere.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Montagne','montagne','montagne.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Musculation','musculation','musculation.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Musique','musique','musique.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Parachute','parachute','parachute.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Patinage','patinage','patinage.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Patisserie','patisserie','patisserie.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Pêche','peche','peche.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Peinture','peinture','peinture.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Photographie','photographie','photographie.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Piano','piano','piano.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Plongée','plongee','plongee.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Poker','poker','poker.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Rugby','rugby','rugby.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Ski','ski','ski.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Tambour','tambour','tambour.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Tennis','tennis','tennis.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Théâtre','theatre','theatre.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Tricot','tricot','tricot.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Vélo','velo','velo.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Vidéo','video','video.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Volley','volley','volley.svg')");
        $this->addSql("INSERT INTO hobby (name, slug, icon) VALUES ('Voyage','voyage','voyage.svg')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM users_hobbies;');
        $this->addSql('DELETE FROM TABLE hobby');
    }
}

