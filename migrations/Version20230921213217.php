<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921213217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compte_bancaire (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, intitule VARCHAR(255) NOT NULL, numero VARCHAR(255) NOT NULL, banque VARCHAR(255) NOT NULL, code_swift VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', code_monnaie VARCHAR(255) NOT NULL, INDEX IDX_50BC21DEA4AEAFEA (entreprise_id), INDEX IDX_50BC21DEFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facture_compte_bancaire (facture_id INT NOT NULL, compte_bancaire_id INT NOT NULL, INDEX IDX_98CB295B7F2DEE08 (facture_id), INDEX IDX_98CB295BAF1E371E (compte_bancaire_id), PRIMARY KEY(facture_id, compte_bancaire_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, facture_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, piece_id INT DEFAULT NULL, paid_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', montant DOUBLE PRECISION NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1DC7A1E7F2DEE08 (facture_id), INDEX IDX_B1DC7A1EA4AEAFEA (entreprise_id), INDEX IDX_B1DC7A1EFB88E14F (utilisateur_id), INDEX IDX_B1DC7A1EC40FCFA8 (piece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE police_facture (police_id INT NOT NULL, facture_id INT NOT NULL, INDEX IDX_D75E6D4137E60BE1 (police_id), INDEX IDX_D75E6D417F2DEE08 (facture_id), PRIMARY KEY(police_id, facture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compte_bancaire ADD CONSTRAINT FK_50BC21DEA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE compte_bancaire ADD CONSTRAINT FK_50BC21DEFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE facture_compte_bancaire ADD CONSTRAINT FK_98CB295B7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facture_compte_bancaire ADD CONSTRAINT FK_98CB295BAF1E371E FOREIGN KEY (compte_bancaire_id) REFERENCES compte_bancaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EC40FCFA8 FOREIGN KEY (piece_id) REFERENCES doc_piece (id)');
        $this->addSql('ALTER TABLE police_facture ADD CONSTRAINT FK_D75E6D4137E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_facture ADD CONSTRAINT FK_D75E6D417F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preference ADD fin_compte_bancaires LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_paiement LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compte_bancaire DROP FOREIGN KEY FK_50BC21DEA4AEAFEA');
        $this->addSql('ALTER TABLE compte_bancaire DROP FOREIGN KEY FK_50BC21DEFB88E14F');
        $this->addSql('ALTER TABLE facture_compte_bancaire DROP FOREIGN KEY FK_98CB295B7F2DEE08');
        $this->addSql('ALTER TABLE facture_compte_bancaire DROP FOREIGN KEY FK_98CB295BAF1E371E');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E7F2DEE08');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EA4AEAFEA');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EFB88E14F');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EC40FCFA8');
        $this->addSql('ALTER TABLE police_facture DROP FOREIGN KEY FK_D75E6D4137E60BE1');
        $this->addSql('ALTER TABLE police_facture DROP FOREIGN KEY FK_D75E6D417F2DEE08');
        $this->addSql('DROP TABLE compte_bancaire');
        $this->addSql('DROP TABLE facture_compte_bancaire');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE police_facture');
        $this->addSql('ALTER TABLE preference DROP fin_compte_bancaires, DROP fin_paiement');
    }
}
