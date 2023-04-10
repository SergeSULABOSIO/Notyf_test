<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410155309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, piste_id INT DEFAULT NULL, mission LONGTEXT NOT NULL, objectif VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_47CC8C92FB88E14F (utilisateur_id), INDEX IDX_47CC8C92A4AEAFEA (entreprise_id), INDEX IDX_47CC8C92C34065BC (piste_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE action_utilisateur (action_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_66CEEA779D32F035 (action_id), INDEX IDX_66CEEA77FB88E14F (utilisateur_id), PRIMARY KEY(action_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cotation (id INT AUTO_INCREMENT NOT NULL, risque_id INT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, piste_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prime_totale DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_996DA9444ECC2413 (risque_id), INDEX IDX_996DA944FB88E14F (utilisateur_id), INDEX IDX_996DA944A4AEAFEA (entreprise_id), INDEX IDX_996DA944C34065BC (piste_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cotation_assureur (cotation_id INT NOT NULL, assureur_id INT NOT NULL, INDEX IDX_B3BAD8375D14FAF0 (cotation_id), INDEX IDX_B3BAD83780F7E20A (assureur_id), PRIMARY KEY(cotation_id, assureur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etape_crm (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5823919EFB88E14F (utilisateur_id), INDEX IDX_5823919EA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE piste (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, etape_id INT NOT NULL, nom VARCHAR(255) NOT NULL, objectif VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', montant DOUBLE PRECISION DEFAULT NULL, expired_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_59E25077FB88E14F (utilisateur_id), INDEX IDX_59E25077A4AEAFEA (entreprise_id), INDEX IDX_59E250774A8CA2AD (etape_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE piste_contact (piste_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_D3E56148C34065BC (piste_id), INDEX IDX_D3E56148E7A1254A (contact_id), PRIMARY KEY(piste_id, contact_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C92FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C92A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C92C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('ALTER TABLE action_utilisateur ADD CONSTRAINT FK_66CEEA779D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_utilisateur ADD CONSTRAINT FK_66CEEA77FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA9444ECC2413 FOREIGN KEY (risque_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('ALTER TABLE cotation_assureur ADD CONSTRAINT FK_B3BAD8375D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cotation_assureur ADD CONSTRAINT FK_B3BAD83780F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etape_crm ADD CONSTRAINT FK_5823919EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE etape_crm ADD CONSTRAINT FK_5823919EA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E25077FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E25077A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E250774A8CA2AD FOREIGN KEY (etape_id) REFERENCES etape_crm (id)');
        $this->addSql('ALTER TABLE piste_contact ADD CONSTRAINT FK_D3E56148C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piste_contact ADD CONSTRAINT FK_D3E56148E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action DROP FOREIGN KEY FK_47CC8C92FB88E14F');
        $this->addSql('ALTER TABLE action DROP FOREIGN KEY FK_47CC8C92A4AEAFEA');
        $this->addSql('ALTER TABLE action DROP FOREIGN KEY FK_47CC8C92C34065BC');
        $this->addSql('ALTER TABLE action_utilisateur DROP FOREIGN KEY FK_66CEEA779D32F035');
        $this->addSql('ALTER TABLE action_utilisateur DROP FOREIGN KEY FK_66CEEA77FB88E14F');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA9444ECC2413');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944FB88E14F');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944A4AEAFEA');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944C34065BC');
        $this->addSql('ALTER TABLE cotation_assureur DROP FOREIGN KEY FK_B3BAD8375D14FAF0');
        $this->addSql('ALTER TABLE cotation_assureur DROP FOREIGN KEY FK_B3BAD83780F7E20A');
        $this->addSql('ALTER TABLE etape_crm DROP FOREIGN KEY FK_5823919EFB88E14F');
        $this->addSql('ALTER TABLE etape_crm DROP FOREIGN KEY FK_5823919EA4AEAFEA');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E25077FB88E14F');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E25077A4AEAFEA');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E250774A8CA2AD');
        $this->addSql('ALTER TABLE piste_contact DROP FOREIGN KEY FK_D3E56148C34065BC');
        $this->addSql('ALTER TABLE piste_contact DROP FOREIGN KEY FK_D3E56148E7A1254A');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE action_utilisateur');
        $this->addSql('DROP TABLE cotation');
        $this->addSql('DROP TABLE cotation_assureur');
        $this->addSql('DROP TABLE etape_crm');
        $this->addSql('DROP TABLE piste');
        $this->addSql('DROP TABLE piste_contact');
    }
}
