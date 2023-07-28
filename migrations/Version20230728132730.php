<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230728132730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece DROP FOREIGN KEY FK_E6CC2951783F755');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece DROP FOREIGN KEY FK_E6CC2951A5BBD2F3');
        $this->addSql('DROP TABLE paiement_taxe_doc_piece');
        $this->addSql('ALTER TABLE paiement_taxe ADD piece_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A9086544C40FCFA8 FOREIGN KEY (piece_id) REFERENCES doc_piece (id)');
        $this->addSql('CREATE INDEX IDX_A9086544C40FCFA8 ON paiement_taxe (piece_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paiement_taxe_doc_piece (paiement_taxe_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_E6CC2951783F755 (paiement_taxe_id), INDEX IDX_E6CC2951A5BBD2F3 (doc_piece_id), PRIMARY KEY(paiement_taxe_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece ADD CONSTRAINT FK_E6CC2951783F755 FOREIGN KEY (paiement_taxe_id) REFERENCES paiement_taxe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_taxe_doc_piece ADD CONSTRAINT FK_E6CC2951A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A9086544C40FCFA8');
        $this->addSql('DROP INDEX IDX_A9086544C40FCFA8 ON paiement_taxe');
        $this->addSql('ALTER TABLE paiement_taxe DROP piece_id');
    }
}
