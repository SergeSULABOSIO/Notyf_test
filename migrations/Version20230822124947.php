<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230822124947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, partenaire_id INT DEFAULT NULL, assureur_id INT DEFAULT NULL, piece_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type INT NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_FE866410FB88E14F (utilisateur_id), INDEX IDX_FE866410A4AEAFEA (entreprise_id), INDEX IDX_FE86641098DE13AC (partenaire_id), INDEX IDX_FE86641080F7E20A (assureur_id), UNIQUE INDEX UNIQ_FE866410C40FCFA8 (piece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE866410FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE866410A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641098DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641080F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE866410C40FCFA8 FOREIGN KEY (piece_id) REFERENCES doc_piece (id)');
        $this->addSql('ALTER TABLE element_facture ADD facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D24447F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_96D24447F2DEE08 ON element_facture (facture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D24447F2DEE08');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE866410FB88E14F');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE866410A4AEAFEA');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641098DE13AC');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641080F7E20A');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE866410C40FCFA8');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP INDEX IDX_96D24447F2DEE08 ON element_facture');
        $this->addSql('ALTER TABLE element_facture DROP facture_id');
    }
}
