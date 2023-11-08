<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108115850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE revenu (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, type INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', partageable INT NOT NULL, taxable INT NOT NULL, base INT NOT NULL, taux DOUBLE PRECISION NOT NULL, montant DOUBLE PRECISION NOT NULL, INDEX IDX_7DA3C045FB88E14F (utilisateur_id), INDEX IDX_7DA3C045A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE revenu ADD CONSTRAINT FK_7DA3C045FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE revenu ADD CONSTRAINT FK_7DA3C045A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenu DROP FOREIGN KEY FK_7DA3C045FB88E14F');
        $this->addSql('ALTER TABLE revenu DROP FOREIGN KEY FK_7DA3C045A4AEAFEA');
        $this->addSql('DROP TABLE revenu');
    }
}
