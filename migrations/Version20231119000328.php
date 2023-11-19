<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119000328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD abonnement TINYINT(1) NOT NULL, ADD obligatoire TINYINT(1) NOT NULL, ADD iard TINYINT(1) NOT NULL, DROP isobligatoire, DROP isabonnement, DROP categorie');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD isobligatoire TINYINT(1) NOT NULL, ADD isabonnement TINYINT(1) NOT NULL, ADD categorie INT NOT NULL, DROP abonnement, DROP obligatoire, DROP iard');
    }
}
