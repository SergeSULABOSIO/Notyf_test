<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231113091449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partenaire ADD piste_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT FK_32FFA373C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('CREATE INDEX IDX_32FFA373C34065BC ON partenaire (piste_id)');
        $this->addSql('ALTER TABLE piste ADD partenaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E2507798DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('CREATE INDEX IDX_59E2507798DE13AC ON piste (partenaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY FK_32FFA373C34065BC');
        $this->addSql('DROP INDEX IDX_32FFA373C34065BC ON partenaire');
        $this->addSql('ALTER TABLE partenaire DROP piste_id');
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E2507798DE13AC');
        $this->addSql('DROP INDEX IDX_59E2507798DE13AC ON piste');
        $this->addSql('ALTER TABLE piste DROP partenaire_id');
    }
}
