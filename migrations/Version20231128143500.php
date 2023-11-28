<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231128143500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece ADD police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B23667537E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('CREATE INDEX IDX_2B23667537E60BE1 ON doc_piece (police_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B23667537E60BE1');
        $this->addSql('DROP INDEX IDX_2B23667537E60BE1 ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP police_id');
    }
}
