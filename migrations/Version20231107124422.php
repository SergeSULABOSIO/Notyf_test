<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107124422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944F347EFB');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA94419EB6921');
        $this->addSql('DROP INDEX IDX_996DA944F347EFB ON cotation');
        $this->addSql('DROP INDEX IDX_996DA94419EB6921 ON cotation');
        $this->addSql('ALTER TABLE cotation ADD validated INT DEFAULT NULL, DROP produit_id, DROP client_id, DROP typeavenant');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation ADD client_id INT DEFAULT NULL, ADD typeavenant VARCHAR(255) DEFAULT NULL, CHANGE validated produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA94419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_996DA944F347EFB ON cotation (produit_id)');
        $this->addSql('CREATE INDEX IDX_996DA94419EB6921 ON cotation (client_id)');
    }
}
