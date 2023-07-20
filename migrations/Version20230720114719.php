<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230720114719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police_doc_piece DROP FOREIGN KEY FK_F15F9B1CA5BBD2F3');
        $this->addSql('ALTER TABLE police_doc_piece DROP FOREIGN KEY FK_F15F9B1C37E60BE1');
        $this->addSql('DROP TABLE police_doc_piece');
        $this->addSql('ALTER TABLE doc_piece ADD police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B23667537E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('CREATE INDEX IDX_2B23667537E60BE1 ON doc_piece (police_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE police_doc_piece (police_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_F15F9B1C37E60BE1 (police_id), INDEX IDX_F15F9B1CA5BBD2F3 (doc_piece_id), PRIMARY KEY(police_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE police_doc_piece ADD CONSTRAINT FK_F15F9B1CA5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_doc_piece ADD CONSTRAINT FK_F15F9B1C37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B23667537E60BE1');
        $this->addSql('DROP INDEX IDX_2B23667537E60BE1 ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP police_id');
    }
}
