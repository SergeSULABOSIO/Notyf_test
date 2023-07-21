<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230721124343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_piste (contact_id INT NOT NULL, piste_id INT NOT NULL, INDEX IDX_74C84078E7A1254A (contact_id), INDEX IDX_74C84078C34065BC (piste_id), PRIMARY KEY(contact_id, piste_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact_piste ADD CONSTRAINT FK_74C84078E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_piste ADD CONSTRAINT FK_74C84078C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638C34065BC');
        $this->addSql('DROP INDEX IDX_4C62E638C34065BC ON contact');
        $this->addSql('ALTER TABLE contact DROP piste_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_piste DROP FOREIGN KEY FK_74C84078E7A1254A');
        $this->addSql('ALTER TABLE contact_piste DROP FOREIGN KEY FK_74C84078C34065BC');
        $this->addSql('DROP TABLE contact_piste');
        $this->addSql('ALTER TABLE contact ADD piste_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('CREATE INDEX IDX_4C62E638C34065BC ON contact (piste_id)');
    }
}
