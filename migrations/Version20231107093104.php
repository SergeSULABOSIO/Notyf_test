<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107093104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD piste_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('CREATE INDEX IDX_C7440455C34065BC ON client (piste_id)');
        $this->addSql('ALTER TABLE piste ADD client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E2507719EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_59E2507719EB6921 ON piste (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455C34065BC');
        $this->addSql('DROP INDEX IDX_C7440455C34065BC ON client');
        $this->addSql('ALTER TABLE client DROP piste_id');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E2507719EB6921');
        $this->addSql('DROP INDEX IDX_59E2507719EB6921 ON piste');
        $this->addSql('ALTER TABLE piste DROP client_id');
    }
}
