<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410104325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre ADD occured_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP date_incident, DROP date_payement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre ADD date_incident DATETIME NOT NULL, ADD date_payement DATETIME DEFAULT NULL, DROP occured_at, DROP paid_at');
    }
}
