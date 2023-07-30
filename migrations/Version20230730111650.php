<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230730111650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm ADD sinistre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('CREATE INDEX IDX_A62034BA216966DF ON action_crm (sinistre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA216966DF');
        $this->addSql('DROP INDEX IDX_A62034BA216966DF ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP sinistre_id');
    }
}
