<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406145812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, code VARCHAR(4) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE assureur (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, siteweb VARCHAR(255) DEFAULT NULL, rccm VARCHAR(255) DEFAULT NULL, idnat VARCHAR(255) DEFAULT NULL, licence VARCHAR(255) DEFAULT NULL, numimpot VARCHAR(255) DEFAULT NULL, isreassureur TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7B0E5955A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE automobile (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, monnaie_id INT NOT NULL, model VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, annee VARCHAR(255) NOT NULL, puissance VARCHAR(255) NOT NULL, valeur NUMERIC(10, 2) DEFAULT NULL, nbsieges INT NOT NULL, utilite VARCHAR(255) NOT NULL, nature INT NOT NULL, plaque VARCHAR(255) NOT NULL, chassis VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BFCEA087A4AEAFEA (entreprise_id), INDEX IDX_BFCEA08798D3FE22 (monnaie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE automobile_police (automobile_id INT NOT NULL, police_id INT NOT NULL, INDEX IDX_5AD6733E50E09BD4 (automobile_id), INDEX IDX_5AD6733E37E60BE1 (police_id), PRIMARY KEY(automobile_id, police_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, siteweb VARCHAR(255) DEFAULT NULL, ispersonnemorale TINYINT(1) NOT NULL, rccm VARCHAR(255) DEFAULT NULL, idnat VARCHAR(255) DEFAULT NULL, numipot VARCHAR(255) DEFAULT NULL, secteur INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C7440455A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, client_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, poste VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4C62E638A4AEAFEA (entreprise_id), INDEX IDX_4C62E63819EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entree_stock (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, date DATETIME NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D938783A7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, rccm VARCHAR(255) DEFAULT NULL, idnat VARCHAR(255) DEFAULT NULL, numimpot VARCHAR(255) DEFAULT NULL, secteur INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monnaie (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, tauxusd NUMERIC(10, 2) NOT NULL, islocale TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B3A6E2E6A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_commission (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, monnaie_id INT NOT NULL, police_id INT NOT NULL, date DATE NOT NULL, montant NUMERIC(10, 2) NOT NULL, refnotededebit VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8AAA6FA8A4AEAFEA (entreprise_id), INDEX IDX_8AAA6FA898D3FE22 (monnaie_id), INDEX IDX_8AAA6FA837E60BE1 (police_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_partenaire (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, monnaie_id INT NOT NULL, partenaire_id INT NOT NULL, police_id INT NOT NULL, date DATE NOT NULL, montant NUMERIC(10, 2) NOT NULL, refnotededebit VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A430CD83A4AEAFEA (entreprise_id), INDEX IDX_A430CD8398D3FE22 (monnaie_id), INDEX IDX_A430CD8398DE13AC (partenaire_id), INDEX IDX_A430CD8337E60BE1 (police_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_taxe (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, monnaie_id INT NOT NULL, taxe_id INT NOT NULL, police_id INT NOT NULL, date DATE NOT NULL, montant NUMERIC(10, 2) NOT NULL, exercice VARCHAR(255) DEFAULT NULL, refnotededebit VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A9086544A4AEAFEA (entreprise_id), INDEX IDX_A908654498D3FE22 (monnaie_id), INDEX IDX_A90865441AB947A4 (taxe_id), INDEX IDX_A908654437E60BE1 (police_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partenaire (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, part NUMERIC(10, 2) NOT NULL, adresse VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, siteweb VARCHAR(255) DEFAULT NULL, rccm VARCHAR(255) DEFAULT NULL, idnat VARCHAR(255) DEFAULT NULL, numimpot VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_32FFA373A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE police (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, monnaie_id INT NOT NULL, client_id INT NOT NULL, produit_id INT NOT NULL, partenaire_id INT DEFAULT NULL, assureur_id INT NOT NULL, reference VARCHAR(255) NOT NULL, dateoperation DATE NOT NULL, dateemission DATE NOT NULL, dateeffet DATE NOT NULL, dateexpiration DATE NOT NULL, idavenant INT NOT NULL, typeavenant VARCHAR(255) NOT NULL, capital NUMERIC(10, 2) NOT NULL, primenette NUMERIC(10, 2) NOT NULL, fronting NUMERIC(10, 2) NOT NULL, arca NUMERIC(10, 2) NOT NULL, tva NUMERIC(10, 2) NOT NULL, fraisadmin NUMERIC(10, 2) NOT NULL, primetotale NUMERIC(10, 2) NOT NULL, discount NUMERIC(10, 2) NOT NULL, modepaiement VARCHAR(255) NOT NULL, ricom NUMERIC(10, 2) NOT NULL, localcom NUMERIC(10, 2) NOT NULL, frontingcom NUMERIC(10, 2) NOT NULL, remarques VARCHAR(255) DEFAULT NULL, reassureurs VARCHAR(255) DEFAULT NULL, cansharericom TINYINT(1) NOT NULL, cansharelocalcom TINYINT(1) NOT NULL, cansharefrontingcom TINYINT(1) NOT NULL, ricompayableby VARCHAR(255) NOT NULL, localcompayableby VARCHAR(255) NOT NULL, frontingcompayableby VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E47C5959A4AEAFEA (entreprise_id), INDEX IDX_E47C595998D3FE22 (monnaie_id), INDEX IDX_E47C595919EB6921 (client_id), INDEX IDX_E47C5959F347EFB (produit_id), INDEX IDX_E47C595998DE13AC (partenaire_id), INDEX IDX_E47C595980F7E20A (assureur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, tauxarca NUMERIC(10, 2) NOT NULL, isobligatoire TINYINT(1) NOT NULL, isabonnement TINYINT(1) NOT NULL, categorie INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_29A5EC27A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE taxe (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, taux NUMERIC(10, 2) NOT NULL, organisation VARCHAR(255) NOT NULL, payableparcourtier TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_56322FE9A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(50) NOT NULL, pseudo VARCHAR(20) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assureur ADD CONSTRAINT FK_7B0E5955A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE automobile ADD CONSTRAINT FK_BFCEA087A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE automobile ADD CONSTRAINT FK_BFCEA08798D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('ALTER TABLE automobile_police ADD CONSTRAINT FK_5AD6733E50E09BD4 FOREIGN KEY (automobile_id) REFERENCES automobile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE automobile_police ADD CONSTRAINT FK_5AD6733E37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE entree_stock ADD CONSTRAINT FK_D938783A7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE monnaie ADD CONSTRAINT FK_B3A6E2E6A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA8A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA898D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA837E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD83A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD8398D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD8398DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD8337E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A9086544A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A908654498D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A90865441AB947A4 FOREIGN KEY (taxe_id) REFERENCES taxe (id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A908654437E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT FK_32FFA373A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C5959A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595998D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C5959F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595998DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595980F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE taxe ADD CONSTRAINT FK_56322FE9A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assureur DROP FOREIGN KEY FK_7B0E5955A4AEAFEA');
        $this->addSql('ALTER TABLE automobile DROP FOREIGN KEY FK_BFCEA087A4AEAFEA');
        $this->addSql('ALTER TABLE automobile DROP FOREIGN KEY FK_BFCEA08798D3FE22');
        $this->addSql('ALTER TABLE automobile_police DROP FOREIGN KEY FK_5AD6733E50E09BD4');
        $this->addSql('ALTER TABLE automobile_police DROP FOREIGN KEY FK_5AD6733E37E60BE1');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455A4AEAFEA');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638A4AEAFEA');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63819EB6921');
        $this->addSql('ALTER TABLE entree_stock DROP FOREIGN KEY FK_D938783A7294869C');
        $this->addSql('ALTER TABLE monnaie DROP FOREIGN KEY FK_B3A6E2E6A4AEAFEA');
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA8A4AEAFEA');
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA898D3FE22');
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA837E60BE1');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD83A4AEAFEA');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD8398D3FE22');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD8398DE13AC');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD8337E60BE1');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A9086544A4AEAFEA');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A908654498D3FE22');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A90865441AB947A4');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A908654437E60BE1');
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY FK_32FFA373A4AEAFEA');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C5959A4AEAFEA');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595998D3FE22');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595919EB6921');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C5959F347EFB');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595998DE13AC');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595980F7E20A');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A4AEAFEA');
        $this->addSql('ALTER TABLE taxe DROP FOREIGN KEY FK_56322FE9A4AEAFEA');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE assureur');
        $this->addSql('DROP TABLE automobile');
        $this->addSql('DROP TABLE automobile_police');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE entree_stock');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE monnaie');
        $this->addSql('DROP TABLE paiement_commission');
        $this->addSql('DROP TABLE paiement_partenaire');
        $this->addSql('DROP TABLE paiement_taxe');
        $this->addSql('DROP TABLE partenaire');
        $this->addSql('DROP TABLE police');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE taxe');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
