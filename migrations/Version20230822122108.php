<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230822122108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE element_facture (id INT AUTO_INCREMENT NOT NULL, police_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, montant DOUBLE PRECISION DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_96D244437E60BE1 (police_id), INDEX IDX_96D2444A4AEAFEA (entreprise_id), INDEX IDX_96D2444FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D244437E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D2444A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D2444FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D244437E60BE1');
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D2444A4AEAFEA');
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D2444FB88E14F');
        $this->addSql('DROP TABLE element_facture');
    }
}
