<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231221153627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenu ADD date_effet DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_expiration DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_operation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_emition DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenu DROP date_effet, DROP date_expiration, DROP date_operation, DROP date_emition');
    }
}
