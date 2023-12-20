<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220000142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tranche ADD started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_effet DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_expiration DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_operation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_emition DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tranche DROP started_at, DROP ended_at, DROP date_effet, DROP date_expiration, DROP date_operation, DROP date_emition');
    }
}
