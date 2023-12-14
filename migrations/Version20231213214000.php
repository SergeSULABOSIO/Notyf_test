<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231213214000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation CHANGE date_effet date_effet DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_expiration date_expiration DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_operation date_operation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_emition date_emition DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation CHANGE date_effet date_effet DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_expiration date_expiration DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_operation date_operation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_emition date_emition DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
