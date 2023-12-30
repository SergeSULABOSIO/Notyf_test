<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231230214759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP INDEX UNIQ_96D2444B76F6B31, ADD INDEX IDX_96D2444B76F6B31 (tranche_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_facture DROP INDEX IDX_96D2444B76F6B31, ADD UNIQUE INDEX UNIQ_96D2444B76F6B31 (tranche_id)');
    }
}
