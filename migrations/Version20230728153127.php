<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230728153127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece_doc_categorie DROP FOREIGN KEY FK_103D734BE1B65DF');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie DROP FOREIGN KEY FK_103D734BA5BBD2F3');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur DROP FOREIGN KEY FK_22C7EB26A5BBD2F3');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur DROP FOREIGN KEY FK_22C7EB26BF9933D3');
        $this->addSql('DROP TABLE doc_piece_doc_categorie');
        $this->addSql('DROP TABLE doc_piece_doc_classeur');
        $this->addSql('ALTER TABLE doc_piece ADD categorie_id INT DEFAULT NULL, ADD classeur_id INT DEFAULT NULL, ADD fichier VARCHAR(255) DEFAULT NULL, DROP fichier_a, DROP fichier_b, DROP fichier_c, DROP fichier_d');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675BCF5E72D FOREIGN KEY (categorie_id) REFERENCES doc_categorie (id)');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675EC10E96A FOREIGN KEY (classeur_id) REFERENCES doc_classeur (id)');
        $this->addSql('CREATE INDEX IDX_2B236675BCF5E72D ON doc_piece (categorie_id)');
        $this->addSql('CREATE INDEX IDX_2B236675EC10E96A ON doc_piece (classeur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doc_piece_doc_categorie (doc_piece_id INT NOT NULL, doc_categorie_id INT NOT NULL, INDEX IDX_103D734BE1B65DF (doc_categorie_id), INDEX IDX_103D734BA5BBD2F3 (doc_piece_id), PRIMARY KEY(doc_piece_id, doc_categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE doc_piece_doc_classeur (doc_piece_id INT NOT NULL, doc_classeur_id INT NOT NULL, INDEX IDX_22C7EB26BF9933D3 (doc_classeur_id), INDEX IDX_22C7EB26A5BBD2F3 (doc_piece_id), PRIMARY KEY(doc_piece_id, doc_classeur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie ADD CONSTRAINT FK_103D734BE1B65DF FOREIGN KEY (doc_categorie_id) REFERENCES doc_categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_categorie ADD CONSTRAINT FK_103D734BA5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur ADD CONSTRAINT FK_22C7EB26A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_doc_classeur ADD CONSTRAINT FK_22C7EB26BF9933D3 FOREIGN KEY (doc_classeur_id) REFERENCES doc_classeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675BCF5E72D');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675EC10E96A');
        $this->addSql('DROP INDEX IDX_2B236675BCF5E72D ON doc_piece');
        $this->addSql('DROP INDEX IDX_2B236675EC10E96A ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece ADD fichier_b VARCHAR(255) DEFAULT NULL, ADD fichier_c VARCHAR(255) DEFAULT NULL, ADD fichier_d VARCHAR(255) DEFAULT NULL, DROP categorie_id, DROP classeur_id, CHANGE fichier fichier_a VARCHAR(255) DEFAULT NULL');
    }
}
