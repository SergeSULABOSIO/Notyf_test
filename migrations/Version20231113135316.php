<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231113135316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chargement ADD cotation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chargement ADD CONSTRAINT FK_363287585D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('CREATE INDEX IDX_363287585D14FAF0 ON chargement (cotation_id)');
        $this->addSql('ALTER TABLE cotation DROP prime_totale, DROP prime_nette, DROP fronting, DROP accessoires, DROP taxes, DROP frais_arca');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chargement DROP FOREIGN KEY FK_363287585D14FAF0');
        $this->addSql('DROP INDEX IDX_363287585D14FAF0 ON chargement');
        $this->addSql('ALTER TABLE chargement DROP cotation_id');
        $this->addSql('ALTER TABLE cotation ADD prime_totale DOUBLE PRECISION NOT NULL, ADD prime_nette DOUBLE PRECISION DEFAULT NULL, ADD fronting DOUBLE PRECISION DEFAULT NULL, ADD accessoires DOUBLE PRECISION DEFAULT NULL, ADD taxes DOUBLE PRECISION DEFAULT NULL, ADD frais_arca DOUBLE PRECISION DEFAULT NULL');
    }
}
