<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727122248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre_doc_piece DROP FOREIGN KEY FK_FBAD7697216966DF');
        $this->addSql('ALTER TABLE sinistre_doc_piece DROP FOREIGN KEY FK_FBAD7697A5BBD2F3');
        $this->addSql('DROP TABLE sinistre_doc_piece');
        $this->addSql('ALTER TABLE doc_piece ADD sinistre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B236675216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('CREATE INDEX IDX_2B236675216966DF ON doc_piece (sinistre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sinistre_doc_piece (sinistre_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_FBAD7697216966DF (sinistre_id), INDEX IDX_FBAD7697A5BBD2F3 (doc_piece_id), PRIMARY KEY(sinistre_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sinistre_doc_piece ADD CONSTRAINT FK_FBAD7697216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_doc_piece ADD CONSTRAINT FK_FBAD7697A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B236675216966DF');
        $this->addSql('DROP INDEX IDX_2B236675216966DF ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP sinistre_id');
    }
}
