<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120221958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police DROP INDEX IDX_E47C59595D14FAF0, ADD UNIQUE INDEX UNIQ_E47C59595D14FAF0 (cotation_id)');
        $this->addSql('ALTER TABLE police ADD piste_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE police ADD CONSTRAINT FK_E47C5959C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('CREATE INDEX IDX_E47C5959C34065BC ON police (piste_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police DROP INDEX UNIQ_E47C59595D14FAF0, ADD INDEX IDX_E47C59595D14FAF0 (cotation_id)');
        $this->addSql('ALTER TABLE police DROP FOREIGN KEY FK_E47C5959C34065BC');
        $this->addSql('DROP INDEX IDX_E47C5959C34065BC ON police');
        $this->addSql('ALTER TABLE police DROP piste_id');
    }
}
