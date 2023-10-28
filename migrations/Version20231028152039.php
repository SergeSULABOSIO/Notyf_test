<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231028152039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EC40FCFA8');
        $this->addSql('DROP INDEX IDX_B1DC7A1EC40FCFA8 ON paiement');
        $this->addSql('ALTER TABLE paiement DROP piece_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement ADD piece_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EC40FCFA8 FOREIGN KEY (piece_id) REFERENCES doc_piece (id)');
        $this->addSql('CREATE INDEX IDX_B1DC7A1EC40FCFA8 ON paiement (piece_id)');
    }
}
