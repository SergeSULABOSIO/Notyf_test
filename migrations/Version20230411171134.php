<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411171134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action_crm (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, piste_id INT DEFAULT NULL, mission LONGTEXT NOT NULL, objectif VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A62034BAFB88E14F (utilisateur_id), INDEX IDX_A62034BAA4AEAFEA (entreprise_id), INDEX IDX_A62034BAC34065BC (piste_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE action_crm_utilisateur (action_crm_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_9D28A0E02ECC6D6F (action_crm_id), INDEX IDX_9D28A0E0FB88E14F (utilisateur_id), PRIMARY KEY(action_crm_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BAA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BAC34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('ALTER TABLE action_crm_utilisateur ADD CONSTRAINT FK_9D28A0E02ECC6D6F FOREIGN KEY (action_crm_id) REFERENCES action_crm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_crm_utilisateur ADD CONSTRAINT FK_9D28A0E0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_utilisateur DROP FOREIGN KEY FK_66CEEA77FB88E14F');
        $this->addSql('ALTER TABLE action_utilisateur DROP FOREIGN KEY FK_66CEEA779D32F035');
        $this->addSql('DROP TABLE action_utilisateur');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action_utilisateur (action_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_66CEEA779D32F035 (action_id), INDEX IDX_66CEEA77FB88E14F (utilisateur_id), PRIMARY KEY(action_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE action_utilisateur ADD CONSTRAINT FK_66CEEA77FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_utilisateur ADD CONSTRAINT FK_66CEEA779D32F035 FOREIGN KEY (action_id) REFERENCES actioncrm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BAFB88E14F');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BAA4AEAFEA');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BAC34065BC');
        $this->addSql('ALTER TABLE action_crm_utilisateur DROP FOREIGN KEY FK_9D28A0E02ECC6D6F');
        $this->addSql('ALTER TABLE action_crm_utilisateur DROP FOREIGN KEY FK_9D28A0E0FB88E14F');
        $this->addSql('DROP TABLE action_crm');
        $this->addSql('DROP TABLE action_crm_utilisateur');
    }
}
