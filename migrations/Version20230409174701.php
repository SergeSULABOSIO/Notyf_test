<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409174701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_sinistre (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, commentaire_precedent_id INT DEFAULT NULL, sinistre_id INT DEFAULT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', message LONGTEXT NOT NULL, INDEX IDX_7BDB2BBFB88E14F (utilisateur_id), INDEX IDX_7BDB2BBA4AEAFEA (entreprise_id), INDEX IDX_7BDB2BBBC311500 (commentaire_precedent_id), INDEX IDX_7BDB2BB216966DF (sinistre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etape_sinistre (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E4F61898A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, siteweb VARCHAR(255) DEFAULT NULL, télephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4F1B9342A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sinistre (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, police_id INT NOT NULL, entreprise_id INT NOT NULL, etape_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', cout DOUBLE PRECISION NOT NULL, date_incident DATETIME NOT NULL, montant_paye DOUBLE PRECISION NOT NULL, date_payement DATETIME DEFAULT NULL, INDEX IDX_F5AC7A67FB88E14F (utilisateur_id), INDEX IDX_F5AC7A6737E60BE1 (police_id), INDEX IDX_F5AC7A67A4AEAFEA (entreprise_id), INDEX IDX_F5AC7A674A8CA2AD (etape_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sinistre_victime (sinistre_id INT NOT NULL, victime_id INT NOT NULL, INDEX IDX_A519E73B216966DF (sinistre_id), INDEX IDX_A519E73B75FF0F4B (victime_id), PRIMARY KEY(sinistre_id, victime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sinistre_expert (sinistre_id INT NOT NULL, expert_id INT NOT NULL, INDEX IDX_56EFBC0F216966DF (sinistre_id), INDEX IDX_56EFBC0FC5568CE4 (expert_id), PRIMARY KEY(sinistre_id, expert_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE victime (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, sinistre_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AD6D2E39A4AEAFEA (entreprise_id), INDEX IDX_AD6D2E39216966DF (sinistre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBBC311500 FOREIGN KEY (commentaire_precedent_id) REFERENCES commentaire_sinistre (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BB216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('ALTER TABLE etape_sinistre ADD CONSTRAINT FK_E4F61898A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE expert ADD CONSTRAINT FK_4F1B9342A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A67FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A6737E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A67A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE sinistre ADD CONSTRAINT FK_F5AC7A674A8CA2AD FOREIGN KEY (etape_id) REFERENCES etape_sinistre (id)');
        $this->addSql('ALTER TABLE sinistre_victime ADD CONSTRAINT FK_A519E73B216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_victime ADD CONSTRAINT FK_A519E73B75FF0F4B FOREIGN KEY (victime_id) REFERENCES victime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_expert ADD CONSTRAINT FK_56EFBC0F216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_expert ADD CONSTRAINT FK_56EFBC0FC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE victime ADD CONSTRAINT FK_AD6D2E39A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE victime ADD CONSTRAINT FK_AD6D2E39216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBFB88E14F');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBA4AEAFEA');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBBC311500');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BB216966DF');
        $this->addSql('ALTER TABLE etape_sinistre DROP FOREIGN KEY FK_E4F61898A4AEAFEA');
        $this->addSql('ALTER TABLE expert DROP FOREIGN KEY FK_4F1B9342A4AEAFEA');
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A67FB88E14F');
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A6737E60BE1');
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A67A4AEAFEA');
        $this->addSql('ALTER TABLE sinistre DROP FOREIGN KEY FK_F5AC7A674A8CA2AD');
        $this->addSql('ALTER TABLE sinistre_victime DROP FOREIGN KEY FK_A519E73B216966DF');
        $this->addSql('ALTER TABLE sinistre_victime DROP FOREIGN KEY FK_A519E73B75FF0F4B');
        $this->addSql('ALTER TABLE sinistre_expert DROP FOREIGN KEY FK_56EFBC0F216966DF');
        $this->addSql('ALTER TABLE sinistre_expert DROP FOREIGN KEY FK_56EFBC0FC5568CE4');
        $this->addSql('ALTER TABLE victime DROP FOREIGN KEY FK_AD6D2E39A4AEAFEA');
        $this->addSql('ALTER TABLE victime DROP FOREIGN KEY FK_AD6D2E39216966DF');
        $this->addSql('DROP TABLE commentaire_sinistre');
        $this->addSql('DROP TABLE etape_sinistre');
        $this->addSql('DROP TABLE expert');
        $this->addSql('DROP TABLE sinistre');
        $this->addSql('DROP TABLE sinistre_victime');
        $this->addSql('DROP TABLE sinistre_expert');
        $this->addSql('DROP TABLE victime');
    }
}
