<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210309105546 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_mesure (id INT AUTO_INCREMENT NOT NULL, mesures_id INT NOT NULL, user_id INT NOT NULL, date DATETIME NOT NULL, cm INT NOT NULL, INDEX IDX_57241A09BA5A0D05 (mesures_id), INDEX IDX_57241A09A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mesures (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, token_recover VARCHAR(255) NOT NULL, password_change_asked TINYINT(1) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, date_naissance DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription_mesure ADD CONSTRAINT FK_57241A09BA5A0D05 FOREIGN KEY (mesures_id) REFERENCES mesures (id)');
        $this->addSql('ALTER TABLE inscription_mesure ADD CONSTRAINT FK_57241A09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_mesure DROP FOREIGN KEY FK_57241A09BA5A0D05');
        $this->addSql('ALTER TABLE inscription_mesure DROP FOREIGN KEY FK_57241A09A76ED395');
        $this->addSql('DROP TABLE inscription_mesure');
        $this->addSql('DROP TABLE mesures');
        $this->addSql('DROP TABLE user');
    }
}
