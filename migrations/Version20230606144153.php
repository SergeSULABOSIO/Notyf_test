<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606144153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE preference (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', crm_taille INT DEFAULT NULL, crm_missions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', crm_feedbacks LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', crm_cotations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', crm_etapes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', crm_pistes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_5D69B053FB88E14F (utilisateur_id), INDEX IDX_5D69B053A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE preference ADD CONSTRAINT FK_5D69B053FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE preference ADD CONSTRAINT FK_5D69B053A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE preference DROP FOREIGN KEY FK_5D69B053FB88E14F');
        $this->addSql('ALTER TABLE preference DROP FOREIGN KEY FK_5D69B053A4AEAFEA');
        $this->addSql('DROP TABLE preference');
    }
}
