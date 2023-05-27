<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230527132800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police ADD gestionnaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C59596885AC1B FOREIGN KEY (gestionnaire_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E47C59596885AC1B ON police (gestionnaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C59596885AC1B');
        $this->addSql('DROP INDEX IDX_E47C59596885AC1B ON police');
        $this->addSql('ALTER TABLE police DROP gestionnaire_id');
    }
}
