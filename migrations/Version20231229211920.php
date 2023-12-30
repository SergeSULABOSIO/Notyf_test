<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231229211920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D244437E60BE1');
        $this->addSql('DROP INDEX IDX_96D244437E60BE1 ON element_facture');
        $this->addSql('ALTER TABLE element_facture CHANGE police_id tranche_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D2444B76F6B31 FOREIGN KEY (tranche_id) REFERENCES tranche (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_96D2444B76F6B31 ON element_facture (tranche_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D2444B76F6B31');
        $this->addSql('DROP INDEX UNIQ_96D2444B76F6B31 ON element_facture');
        $this->addSql('ALTER TABLE element_facture CHANGE tranche_id police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D244437E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('CREATE INDEX IDX_96D244437E60BE1 ON element_facture (police_id)');
    }
}
