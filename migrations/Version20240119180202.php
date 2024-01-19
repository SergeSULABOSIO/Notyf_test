<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240119180202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece ADD paiement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B2366752A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)');
        $this->addSql('CREATE INDEX IDX_2B2366752A4C4478 ON doc_piece (paiement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B2366752A4C4478');
        $this->addSql('DROP INDEX IDX_2B2366752A4C4478 ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP paiement_id');
    }
}
