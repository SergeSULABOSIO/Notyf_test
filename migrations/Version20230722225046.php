<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230722225046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA9444ECC2413');
        $this->addSql('DROP INDEX IDX_996DA9444ECC2413 ON cotation');
        $this->addSql('ALTER TABLE cotation DROP risque_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation ADD risque_id INT NOT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA9444ECC2413 FOREIGN KEY (risque_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_996DA9444ECC2413 ON cotation (risque_id)');
    }
}
