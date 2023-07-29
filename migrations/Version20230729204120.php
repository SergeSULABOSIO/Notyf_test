<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230729204120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm ADD cotation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA5D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('CREATE INDEX IDX_A62034BA5D14FAF0 ON action_crm (cotation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA5D14FAF0');
        $this->addSql('DROP INDEX IDX_A62034BA5D14FAF0 ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP cotation_id');
    }
}
