<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230729185854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm ADD police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('CREATE INDEX IDX_A62034BA37E60BE1 ON action_crm (police_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA37E60BE1');
        $this->addSql('DROP INDEX IDX_A62034BA37E60BE1 ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP police_id');
    }
}
