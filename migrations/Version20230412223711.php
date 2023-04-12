<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230412223711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A6798D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_F5AC7A6798D3FE22 ON sinistre (monnaie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A6798D3FE22');
        $this->addSql('DROP INDEX IDX_F5AC7A6798D3FE22 ON sinistre');
        $this->addSql('ALTER TABLE sinistre DROP monnaie_id');
    }
}
