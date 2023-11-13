<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231113133426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chargement (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, type INT NOT NULL, description VARCHAR(255) DEFAULT NULL, montant DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_36328758FB88E14F (utilisateur_id), INDEX IDX_36328758A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chargement ADD CONSTRAINT FK_36328758FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE chargement ADD CONSTRAINT FK_36328758A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chargement DROP FOREIGN KEY FK_36328758FB88E14F');
        $this->addSql('ALTER TABLE chargement DROP FOREIGN KEY FK_36328758A4AEAFEA');
        $this->addSql('DROP TABLE chargement');
    }
}
