<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410142443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doc_piece_doc_categorie (doc_piece_id INT NOT NULL, doc_categorie_id INT NOT NULL, INDEX IDX_103D734BA5BBD2F3 (doc_piece_id), INDEX IDX_103D734BE1B65DF (doc_categorie_id), PRIMARY KEY(doc_piece_id, doc_categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doc_piece_doc_classeur (doc_piece_id INT NOT NULL, doc_classeur_id INT NOT NULL, INDEX IDX_22C7EB26A5BBD2F3 (doc_piece_id), INDEX IDX_22C7EB26BF9933D3 (doc_classeur_id), PRIMARY KEY(doc_piece_id, doc_classeur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_commission_doc_piece (paiement_commission_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_48018DC0654CF4FC (paiement_commission_id), INDEX IDX_48018DC0A5BBD2F3 (doc_piece_id), PRIMARY KEY(paiement_commission_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_partenaire_doc_piece (paiement_partenaire_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_142C3575DDBFF9E2 (paiement_partenaire_id), INDEX IDX_142C3575A5BBD2F3 (doc_piece_id), PRIMARY KEY(paiement_partenaire_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement_taxe_doc_piece (paiement_taxe_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_E6CC2951783F755 (paiement_taxe_id), INDEX IDX_E6CC2951A5BBD2F3 (doc_piece_id), PRIMARY KEY(paiement_taxe_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE police_doc_piece (police_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_F15F9B1C37E60BE1 (police_id), INDEX IDX_F15F9B1CA5BBD2F3 (doc_piece_id), PRIMARY KEY(police_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sinistre_doc_piece (sinistre_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_FBAD7697216966DF (sinistre_id), INDEX IDX_FBAD7697A5BBD2F3 (doc_piece_id), PRIMARY KEY(sinistre_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie ADD CONSTRAINT FK_103D734BA5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie ADD CONSTRAINT FK_103D734BE1B65DF FOREIGN KEY (doc_categorie_id) REFERENCES doc_categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur ADD CONSTRAINT FK_22C7EB26A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur ADD CONSTRAINT FK_22C7EB26BF9933D3 FOREIGN KEY (doc_classeur_id) REFERENCES doc_classeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece ADD CONSTRAINT FK_48018DC0654CF4FC FOREIGN KEY (paiement_commission_id) REFERENCES paiement_commission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece ADD CONSTRAINT FK_48018DC0A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_partenaire_doc_piece ADD CONSTRAINT FK_142C3575DDBFF9E2 FOREIGN KEY (paiement_partenaire_id) REFERENCES paiement_partenaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_partenaire_doc_piece ADD CONSTRAINT FK_142C3575A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece ADD CONSTRAINT FK_E6CC2951783F755 FOREIGN KEY (paiement_taxe_id) REFERENCES paiement_taxe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece ADD CONSTRAINT FK_E6CC2951A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_doc_piece ADD CONSTRAINT FK_F15F9B1C37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_doc_piece ADD CONSTRAINT FK_F15F9B1CA5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_doc_piece ADD CONSTRAINT FK_FBAD7697216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_doc_piece ADD CONSTRAINT FK_FBAD7697A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece_doc_categorie DROP FOREIGN KEY FK_103D734BA5BBD2F3');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie DROP FOREIGN KEY FK_103D734BE1B65DF');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur DROP FOREIGN KEY FK_22C7EB26A5BBD2F3');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur DROP FOREIGN KEY FK_22C7EB26BF9933D3');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece DROP FOREIGN KEY FK_48018DC0654CF4FC');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece DROP FOREIGN KEY FK_48018DC0A5BBD2F3');
        $this->addSql('ALTER TABLE paiement_partenaire_doc_piece DROP FOREIGN KEY FK_142C3575DDBFF9E2');
        $this->addSql('ALTER TABLE paiement_partenaire_doc_piece DROP FOREIGN KEY FK_142C3575A5BBD2F3');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece DROP FOREIGN KEY FK_E6CC2951783F755');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece DROP FOREIGN KEY FK_E6CC2951A5BBD2F3');
        $this->addSql('ALTER TABLE police_doc_piece DROP FOREIGN KEY FK_F15F9B1C37E60BE1');
        $this->addSql('ALTER TABLE police_doc_piece DROP FOREIGN KEY FK_F15F9B1CA5BBD2F3');
        $this->addSql('ALTER TABLE sinistre_doc_piece DROP FOREIGN KEY FK_FBAD7697216966DF');
        $this->addSql('ALTER TABLE sinistre_doc_piece DROP FOREIGN KEY FK_FBAD7697A5BBD2F3');
        $this->addSql('DROP TABLE doc_piece_doc_categorie');
        $this->addSql('DROP TABLE doc_piece_doc_classeur');
        $this->addSql('DROP TABLE paiement_commission_doc_piece');
        $this->addSql('DROP TABLE paiement_partenaire_doc_piece');
        $this->addSql('DROP TABLE paiement_taxe_doc_piece');
        $this->addSql('DROP TABLE police_doc_piece');
        $this->addSql('DROP TABLE sinistre_doc_piece');
    }
}
