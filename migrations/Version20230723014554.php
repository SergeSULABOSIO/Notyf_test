<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230723014554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation ADD produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_996DA944F347EFB ON cotation (produit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944F347EFB');
        $this->addSql('DROP INDEX IDX_996DA944F347EFB ON cotation');
        $this->addSql('ALTER TABLE cotation DROP produit_id');
    }
}
