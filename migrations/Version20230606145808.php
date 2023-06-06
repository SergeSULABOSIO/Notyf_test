<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606145808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE preference ADD apparence INT NOT NULL, ADD pro_taille INT NOT NULL, ADD pro_assureurs LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_automobiles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_contacts LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_clients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_partenaires LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_polices LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD pro_produits LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_taille INT NOT NULL, ADD fin_taxes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_monnaies LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_commissions_payees LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_retrocommissions_payees LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD fin_taxes_payees LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sin_taille INT NOT NULL, ADD sin_commentaires LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sin_etapes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sin_experts LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sin_sinistres LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sin_victimes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD bib_taille INT NOT NULL, ADD bib_categories LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD bib_classeurs LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD bib_pieces LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD par_taille INT NOT NULL, ADD par_utilisateurs LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE preference DROP apparence, DROP pro_taille, DROP pro_assureurs, DROP pro_automobiles, DROP pro_contacts, DROP pro_clients, DROP pro_partenaires, DROP pro_polices, DROP pro_produits, DROP fin_taille, DROP fin_taxes, DROP fin_monnaies, DROP fin_commissions_payees, DROP fin_retrocommissions_payees, DROP fin_taxes_payees, DROP sin_taille, DROP sin_commentaires, DROP sin_etapes, DROP sin_experts, DROP sin_sinistres, DROP sin_victimes, DROP bib_taille, DROP bib_categories, DROP bib_classeurs, DROP bib_pieces, DROP par_taille, DROP par_utilisateurs');
    }
}
