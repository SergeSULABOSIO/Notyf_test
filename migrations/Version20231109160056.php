<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231109160056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenu ADD cotation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revenu ADD CONSTRAINT FK_7DA3C0455D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('CREATE INDEX IDX_7DA3C0455D14FAF0 ON revenu (cotation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenu DROP FOREIGN KEY FK_7DA3C0455D14FAF0');
        $this->addSql('DROP INDEX IDX_7DA3C0455D14FAF0 ON revenu');
        $this->addSql('ALTER TABLE revenu DROP cotation_id');
    }
}
