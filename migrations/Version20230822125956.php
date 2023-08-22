<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230822125956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement_commission ADD facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA87F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_8AAA6FA87F2DEE08 ON paiement_commission (facture_id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD837F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_A430CD837F2DEE08 ON paiement_partenaire (facture_id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A90865447F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_A90865447F2DEE08 ON paiement_taxe (facture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA87F2DEE08');
        $this->addSql('DROP INDEX IDX_8AAA6FA87F2DEE08 ON paiement_commission');
        $this->addSql('ALTER TABLE paiement_commission DROP facture_id');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD837F2DEE08');
        $this->addSql('DROP INDEX IDX_A430CD837F2DEE08 ON paiement_partenaire');
        $this->addSql('ALTER TABLE paiement_partenaire DROP facture_id');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A90865447F2DEE08');
        $this->addSql('DROP INDEX IDX_A90865447F2DEE08 ON paiement_taxe');
        $this->addSql('ALTER TABLE paiement_taxe DROP facture_id');
    }
}
