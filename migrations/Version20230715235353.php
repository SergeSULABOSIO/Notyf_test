<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230715235353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entree_stock DROP FOREIGN KEY FK_D938783A7294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE entree_stock');
        $this->addSql('ALTER TABLE automobile DROP FOREIGN KEY FK_BFCEA08798D3FE22');
        $this->addSql('DROP INDEX IDX_BFCEA08798D3FE22 ON automobile');
        $this->addSql('ALTER TABLE automobile DROP monnaie_id');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA94498D3FE22');
        $this->addSql('DROP INDEX IDX_996DA94498D3FE22 ON cotation');
        $this->addSql('ALTER TABLE cotation DROP monnaie_id');
        $this->addSql('ALTER TABLE paiement_commission DROP FOREIGN KEY FK_8AAA6FA898D3FE22');
        $this->addSql('DROP INDEX IDX_8AAA6FA898D3FE22 ON paiement_commission');
        $this->addSql('ALTER TABLE paiement_commission DROP monnaie_id');
        $this->addSql('ALTER TABLE paiement_partenaire DROP FOREIGN KEY FK_A430CD8398D3FE22');
        $this->addSql('DROP INDEX IDX_A430CD8398D3FE22 ON paiement_partenaire');
        $this->addSql('ALTER TABLE paiement_partenaire DROP monnaie_id');
        $this->addSql('ALTER TABLE paiement_taxe DROP FOREIGN KEY FK_A908654498D3FE22');
        $this->addSql('DROP INDEX IDX_A908654498D3FE22 ON paiement_taxe');
        $this->addSql('ALTER TABLE paiement_taxe DROP monnaie_id');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C595998D3FE22');
        $this->addSql('DROP INDEX IDX_E47C595998D3FE22 ON police');
        $this->addSql('ALTER TABLE police DROP monnaie_id');
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A6798D3FE22');
        $this->addSql('DROP INDEX IDX_F5AC7A6798D3FE22 ON sinistre');
        $this->addSql('ALTER TABLE sinistre DROP monnaie_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, prix NUMERIC(10, 2) NOT NULL, code VARCHAR(4) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE entree_stock (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, date DATETIME NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D938783A7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE entree_stock ADD CONSTRAINT FK_D938783A7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE automobile ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE automobile ADD CONSTRAINT FK_BFCEA08798D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_BFCEA08798D3FE22 ON automobile (monnaie_id)');
        $this->addSql('ALTER TABLE cotation ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA94498D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_996DA94498D3FE22 ON cotation (monnaie_id)');
        $this->addSql('ALTER TABLE paiement_commission ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiement_commission ADD CONSTRAINT FK_8AAA6FA898D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_8AAA6FA898D3FE22 ON paiement_commission (monnaie_id)');
        $this->addSql('ALTER TABLE paiement_partenaire ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiement_partenaire ADD CONSTRAINT FK_A430CD8398D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_A430CD8398D3FE22 ON paiement_partenaire (monnaie_id)');
        $this->addSql('ALTER TABLE paiement_taxe ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiement_taxe ADD CONSTRAINT FK_A908654498D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_A908654498D3FE22 ON paiement_taxe (monnaie_id)');
        $this->addSql('ALTER TABLE police ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C595998D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_E47C595998D3FE22 ON police (monnaie_id)');
        $this->addSql('ALTER TABLE sinistre ADD monnaie_id INT NOT NULL');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A6798D3FE22 FOREIGN KEY (monnaie_id) REFERENCES monnaie (id)');
        $this->addSql('CREATE INDEX IDX_F5AC7A6798D3FE22 ON sinistre (monnaie_id)');
    }
}
