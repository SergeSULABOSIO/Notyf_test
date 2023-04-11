<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411223033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA94498D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_996DA94498D3FE22 ON cotation (monnaie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA94498D3FE22');
        $this->addSql('DROP INDEX IDX_996DA94498D3FE22 ON cotation');
        $this->addSql('ALTER TABLE cotation DROP monnaie_id');
    }
}
