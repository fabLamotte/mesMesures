<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210205120307 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_mesure (id INT AUTO_INCREMENT NOT NULL, mesures_id INT NOT NULL, user_id INT NOT NULL, date DATETIME NOT NULL, cm INT NOT NULL, INDEX IDX_57241A09BA5A0D05 (mesures_id), INDEX IDX_57241A09A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mesures (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription_mesure ADD CONSTRAINT FK_57241A09BA5A0D05 FOREIGN KEY (mesures_id) REFERENCES mesures (id)');
        $this->addSql('ALTER TABLE inscription_mesure ADD CONSTRAINT FK_57241A09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_mesure DROP FOREIGN KEY FK_57241A09BA5A0D05');
        $this->addSql('DROP TABLE inscription_mesure');
        $this->addSql('DROP TABLE mesures');
    }
}
