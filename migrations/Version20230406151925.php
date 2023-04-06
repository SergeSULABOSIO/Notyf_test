<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406151925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entreprise_utilisateur (entreprise_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_2A6BCFD8A4AEAFEA (entreprise_id), INDEX IDX_2A6BCFD8FB88E14F (utilisateur_id), PRIMARY KEY(entreprise_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entreprise_utilisateur ADD CONSTRAINT FK_2A6BCFD8A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entreprise_utilisateur ADD CONSTRAINT FK_2A6BCFD8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_utilisateur DROP FOREIGN KEY FK_2A6BCFD8A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_utilisateur DROP FOREIGN KEY FK_2A6BCFD8FB88E14F');
        $this->addSql('DROP TABLE entreprise_utilisateur');
    }
}
