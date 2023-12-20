<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220011314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation ADD assistante_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944A2561908 FOREIGN KEY (assistante_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_996DA944A2561908 ON cotation (assistante_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944A2561908');
        $this->addSql('DROP INDEX IDX_996DA944A2561908 ON cotation');
        $this->addSql('ALTER TABLE cotation DROP assistante_id');
    }
}
