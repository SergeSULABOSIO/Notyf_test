<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240223133054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture ADD include_prime TINYINT(1) DEFAULT NULL, ADD include_com_locale TINYINT(1) DEFAULT NULL, ADD include_com_fronting TINYINT(1) DEFAULT NULL, ADD include_com_reassurance TINYINT(1) DEFAULT NULL, ADD include_frais_gestion TINYINT(1) DEFAULT NULL, ADD include_retro_com TINYINT(1) DEFAULT NULL, ADD include_taxe_courtier TINYINT(1) DEFAULT NULL, ADD include_taxe_assureur TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE facture CHANGE destination destination INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP include_prime, DROP include_com_locale, DROP include_com_fronting, DROP include_com_reassurance, DROP include_frais_gestion, DROP include_retro_com, DROP include_taxe_courtier, DROP include_taxe_assureur');
        $this->addSql('ALTER TABLE facture CHANGE destination destination INT NOT NULL');
    }
}
