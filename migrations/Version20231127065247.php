<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231127065247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675216966DF');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B2366755D14FAF0');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675EC10E96A');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B2366752A4C4478');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B23667537E60BE1');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675BCF5E72D');
        $this->addSql('DROP INDEX IDX_2B236675EC10E96A ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B2366755D14FAF0 ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B2366752A4C4478 ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B236675216966DF ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B236675BCF5E72D ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B23667537E60BE1 ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece ADD type INT DEFAULT NULL, DROP police_id, DROP cotation_id, DROP sinistre_id, DROP categorie_id, DROP classeur_id, DROP paiement_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece ADD cotation_id INT DEFAULT NULL, ADD sinistre_id INT DEFAULT NULL, ADD categorie_id INT DEFAULT NULL, ADD classeur_id INT DEFAULT NULL, ADD paiement_id INT DEFAULT NULL, CHANGE type police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B2366755D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675EC10E96A FOREIGN KEY (classeur_id) REFERENCES doc_classeur (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B2366752A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B23667537E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675BCF5E72D FOREIGN KEY (categorie_id) REFERENCES doc_categorie (id)');
        $this->addSql('CREATE INDEX IDX_2B236675EC10E96A ON doc_piece (classeur_id)');
        $this->addSql('CREATE INDEX IDX_2B2366755D14FAF0 ON doc_piece (cotation_id)');
        $this->addSql('CREATE INDEX IDX_2B2366752A4C4478 ON doc_piece (paiement_id)');
        $this->addSql('CREATE INDEX IDX_2B236675216966DF ON doc_piece (sinistre_id)');
        $this->addSql('CREATE INDEX IDX_2B236675BCF5E72D ON doc_piece (categorie_id)');
        $this->addSql('CREATE INDEX IDX_2B23667537E60BE1 ON doc_piece (police_id)');
    }
}
