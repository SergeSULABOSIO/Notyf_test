<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230826144706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP FOREIGN KEY FK_96D24447F2DEE08');
        $this->addSql('DROP INDEX IDX_96D24447F2DEE08 ON element_facture');
        $this->addSql('ALTER TABLE element_facture DROP facture_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture ADD facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element_facture ADD CONSTRAINT FK_96D24447F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_96D24447F2DEE08 ON element_facture (facture_id)');
    }
}
