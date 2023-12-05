<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231205144812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA5D14FAF0');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA37E60BE1');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA216966DF');
        $this->addSql('DROP INDEX IDX_A62034BA5D14FAF0 ON action_crm');
        $this->addSql('DROP INDEX IDX_A62034BA216966DF ON action_crm');
        $this->addSql('DROP INDEX IDX_A62034BA37E60BE1 ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP police_id, DROP cotation_id, DROP sinistre_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm ADD police_id INT DEFAULT NULL, ADD cotation_id INT DEFAULT NULL, ADD sinistre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA5D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id)');
        $this->addSql('CREATE INDEX IDX_A62034BA5D14FAF0 ON action_crm (cotation_id)');
        $this->addSql('CREATE INDEX IDX_A62034BA216966DF ON action_crm (sinistre_id)');
        $this->addSql('CREATE INDEX IDX_A62034BA37E60BE1 ON action_crm (police_id)');
    }
}
