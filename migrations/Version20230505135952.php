<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230505135952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assureur ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE assureur ADD CONSTRAINT FK_7B0E5955FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_7B0E5955FB88E14F ON assureur (utilisateur_id)');
        $this->addSql('ALTER TABLE automobile ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE automobile ADD CONSTRAINT FK_BFCEA087FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_BFCEA087FB88E14F ON automobile (utilisateur_id)');
        $this->addSql('ALTER TABLE client ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_C7440455FB88E14F ON client (utilisateur_id)');
        $this->addSql('ALTER TABLE contact ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_4C62E638FB88E14F ON contact (utilisateur_id)');
        $this->addSql('ALTER TABLE etape_sinistre ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE etape_sinistre ADD CONSTRAINT FK_E4F61898FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E4F61898FB88E14F ON etape_sinistre (utilisateur_id)');
        $this->addSql('ALTER TABLE expert ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expert ADD CONSTRAINT FK_4F1B9342FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_4F1B9342FB88E14F ON expert (utilisateur_id)');
        $this->addSql('ALTER TABLE monnaie ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE monnaie ADD CONSTRAINT FK_B3A6E2E6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_B3A6E2E6FB88E14F ON monnaie (utilisateur_id)');
        $this->addSql('ALTER TABLE paiement_commission ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_8AAA6FA8FB88E14F ON paiement_commission (utilisateur_id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD83FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_A430CD83FB88E14F ON paiement_partenaire (utilisateur_id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A9086544FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_A9086544FB88E14F ON paiement_taxe (utilisateur_id)');
        $this->addSql('ALTER TABLE partenaire ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT FK_32FFA373FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_32FFA373FB88E14F ON partenaire (utilisateur_id)');
        $this->addSql('ALTER TABLE police ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C5959FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E47C5959FB88E14F ON police (utilisateur_id)');
        $this->addSql('ALTER TABLE produit ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27FB88E14F ON produit (utilisateur_id)');
        $this->addSql('ALTER TABLE taxe ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE taxe ADD CONSTRAINT FK_56322FE9FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_56322FE9FB88E14F ON taxe (utilisateur_id)');
        $this->addSql('ALTER TABLE victime ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE victime ADD CONSTRAINT FK_AD6D2E39FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AD6D2E39FB88E14F ON victime (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assureur DROP FOREIGN KEY FK_7B0E5955FB88E14F');
        $this->addSql('DROP INDEX IDX_7B0E5955FB88E14F ON assureur');
        $this->addSql('ALTER TABLE assureur DROP utilisateur_id');
        $this->addSql('ALTER TABLE automobile DROP FOREIGN KEY FK_BFCEA087FB88E14F');
        $this->addSql('DROP INDEX IDX_BFCEA087FB88E14F ON automobile');
        $this->addSql('ALTER TABLE automobile DROP utilisateur_id');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455FB88E14F');
        $this->addSql('DROP INDEX IDX_C7440455FB88E14F ON client');
        $this->addSql('ALTER TABLE client DROP utilisateur_id');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638FB88E14F');
        $this->addSql('DROP INDEX IDX_4C62E638FB88E14F ON contact');
        $this->addSql('ALTER TABLE contact DROP utilisateur_id');
        $this->addSql('ALTER TABLE etape_sinistre DROP FOREIGN KEY FK_E4F61898FB88E14F');
        $this->addSql('DROP INDEX IDX_E4F61898FB88E14F ON etape_sinistre');
        $this->addSql('ALTER TABLE etape_sinistre DROP utilisateur_id');
        $this->addSql('ALTER TABLE expert DROP FOREIGN KEY FK_4F1B9342FB88E14F');
        $this->addSql('DROP INDEX IDX_4F1B9342FB88E14F ON expert');
        $this->addSql('ALTER TABLE expert DROP utilisateur_id');
        $this->addSql('ALTER TABLE monnaie DROP FOREIGN KEY FK_B3A6E2E6FB88E14F');
        $this->addSql('DROP INDEX IDX_B3A6E2E6FB88E14F ON monnaie');
        $this->addSql('ALTER TABLE monnaie DROP utilisateur_id');
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA8FB88E14F');
        $this->addSql('DROP INDEX IDX_8AAA6FA8FB88E14F ON paiement_commission');
        $this->addSql('ALTER TABLE paiement_commission DROP utilisateur_id');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD83FB88E14F');
        $this->addSql('DROP INDEX IDX_A430CD83FB88E14F ON paiement_partenaire');
        $this->addSql('ALTER TABLE paiement_partenaire DROP utilisateur_id');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A9086544FB88E14F');
        $this->addSql('DROP INDEX IDX_A9086544FB88E14F ON paiement_taxe');
        $this->addSql('ALTER TABLE paiement_taxe DROP utilisateur_id');
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY FK_32FFA373FB88E14F');
        $this->addSql('DROP INDEX IDX_32FFA373FB88E14F ON partenaire');
        $this->addSql('ALTER TABLE partenaire DROP utilisateur_id');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C5959FB88E14F');
        $this->addSql('DROP INDEX IDX_E47C5959FB88E14F ON police');
        $this->addSql('ALTER TABLE police DROP utilisateur_id');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27FB88E14F');
        $this->addSql('DROP INDEX IDX_29A5EC27FB88E14F ON produit');
        $this->addSql('ALTER TABLE produit DROP utilisateur_id');
        $this->addSql('ALTER TABLE taxe DROP FOREIGN KEY FK_56322FE9FB88E14F');
        $this->addSql('DROP INDEX IDX_56322FE9FB88E14F ON taxe');
        $this->addSql('ALTER TABLE taxe DROP utilisateur_id');
        $this->addSql('ALTER TABLE victime DROP FOREIGN KEY FK_AD6D2E39FB88E14F');
        $this->addSql('DROP INDEX IDX_AD6D2E39FB88E14F ON victime');
        $this->addSql('ALTER TABLE victime DROP utilisateur_id');
    }
}
