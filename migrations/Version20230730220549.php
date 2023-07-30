<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230730220549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBBC311500');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BB216966DF');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBFB88E14F');
        $this->addSql('ALTER TABLE commentaire_sinistre DROP FOREIGN KEY FK_7BDB2BBA4AEAFEA');
        $this->addSql('DROP TABLE commentaire_sinistre');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_sinistre (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, entreprise_id INT NOT NULL, commentaire_precedent_id INT DEFAULT NULL, sinistre_id INT DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7BDB2BBBC311500 (commentaire_precedent_id), INDEX IDX_7BDB2BB216966DF (sinistre_id), INDEX IDX_7BDB2BBFB88E14F (utilisateur_id), INDEX IDX_7BDB2BBA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBBC311500 FOREIGN KEY (commentaire_precedent_id) REFERENCES commentaire_sinistre (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BB216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire_sinistre ADD CONSTRAINT FK_7BDB2BBA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }
}
