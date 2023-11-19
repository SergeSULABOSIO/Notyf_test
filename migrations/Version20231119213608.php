<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119213608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste ADD gestionnaire_id INT DEFAULT NULL, ADD assistant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E250776885AC1B FOREIGN KEY (gestionnaire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E25077E05387EF FOREIGN KEY (assistant_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_59E250776885AC1B ON piste (gestionnaire_id)');
        $this->addSql('CREATE INDEX IDX_59E25077E05387EF ON piste (assistant_id)');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595919EB6921');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595980F7E20A');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C5959F347EFB');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C59595D14FAF0');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595998DE13AC');
        $this->addSql('DROP INDEX UNIQ_E47C59595D14FAF0 ON police');
        $this->addSql('DROP INDEX IDX_E47C595998DE13AC ON police');
        $this->addSql('DROP INDEX IDX_E47C595980F7E20A ON police');
        $this->addSql('DROP INDEX IDX_E47C595919EB6921 ON police');
        $this->addSql('DROP INDEX IDX_E47C5959F347EFB ON police');
        $this->addSql('ALTER TABLE police DROP client_id, DROP produit_id, DROP partenaire_id, DROP assureur_id, DROP cotation_id, DROP capital, DROP primenette, DROP fronting, DROP arca, DROP tva, DROP fraisadmin, DROP primetotale, DROP discount, DROP modepaiement, DROP ricom, DROP localcom, DROP frontingcom, DROP remarques, DROP reassureurs, DROP cansharericom, DROP cansharelocalcom, DROP cansharefrontingcom, DROP ricompayableby, DROP localcompayableby, DROP frontingcompayableby, DROP part_exceptionnelle_partenaire, DROP unpaidcommission, DROP unpaidretrocommission, DROP unpaidtaxecourtier, DROP unpaidtaxeassureur, DROP paidcommission, DROP paidretrocommission, DROP paidtaxecourtier, DROP paidtaxeassureur, DROP unpaidtaxe, DROP paidtaxe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E250776885AC1B');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E25077E05387EF');
        $this->addSql('DROP INDEX IDX_59E250776885AC1B ON piste');
        $this->addSql('DROP INDEX IDX_59E25077E05387EF ON piste');
        $this->addSql('ALTER TABLE piste DROP gestionnaire_id, DROP assistant_id');
        $this->addSql('ALTER TABLE police ADD client_id INT DEFAULT NULL, ADD produit_id INT DEFAULT NULL, ADD partenaire_id INT DEFAULT NULL, ADD assureur_id INT DEFAULT NULL, ADD cotation_id INT DEFAULT NULL, ADD capital NUMERIC(10, 2) NOT NULL, ADD primenette NUMERIC(10, 2) NOT NULL, ADD fronting NUMERIC(10, 2) NOT NULL, ADD arca NUMERIC(10, 2) NOT NULL, ADD tva NUMERIC(10, 2) NOT NULL, ADD fraisadmin NUMERIC(10, 2) NOT NULL, ADD primetotale NUMERIC(10, 2) NOT NULL, ADD discount NUMERIC(10, 2) NOT NULL, ADD modepaiement VARCHAR(255) NOT NULL, ADD ricom NUMERIC(10, 2) NOT NULL, ADD localcom NUMERIC(10, 2) NOT NULL, ADD frontingcom NUMERIC(10, 2) NOT NULL, ADD remarques VARCHAR(255) DEFAULT NULL, ADD reassureurs VARCHAR(255) DEFAULT NULL, ADD cansharericom TINYINT(1) NOT NULL, ADD cansharelocalcom TINYINT(1) NOT NULL, ADD cansharefrontingcom TINYINT(1) NOT NULL, ADD ricompayableby VARCHAR(255) NOT NULL, ADD localcompayableby VARCHAR(255) NOT NULL, ADD frontingcompayableby VARCHAR(255) NOT NULL, ADD part_exceptionnelle_partenaire DOUBLE PRECISION DEFAULT NULL, ADD unpaidcommission DOUBLE PRECISION DEFAULT NULL, ADD unpaidretrocommission DOUBLE PRECISION DEFAULT NULL, ADD unpaidtaxecourtier DOUBLE PRECISION DEFAULT NULL, ADD unpaidtaxeassureur DOUBLE PRECISION DEFAULT NULL, ADD paidcommission DOUBLE PRECISION DEFAULT NULL, ADD paidretrocommission DOUBLE PRECISION DEFAULT NULL, ADD paidtaxecourtier DOUBLE PRECISION DEFAULT NULL, ADD paidtaxeassureur DOUBLE PRECISION DEFAULT NULL, ADD unpaidtaxe DOUBLE PRECISION DEFAULT NULL, ADD paidtaxe DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595980F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C5959F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C59595D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595998DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E47C59595D14FAF0 ON police (cotation_id)');
        $this->addSql('CREATE INDEX IDX_E47C595998DE13AC ON police (partenaire_id)');
        $this->addSql('CREATE INDEX IDX_E47C595980F7E20A ON police (assureur_id)');
        $this->addSql('CREATE INDEX IDX_E47C595919EB6921 ON police (client_id)');
        $this->addSql('CREATE INDEX IDX_E47C5959F347EFB ON police (produit_id)');
    }
}
