<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230722225736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation_assureur DROP FOREIGN KEY FK_B3BAD8375D14FAF0');
        $this->addSql('ALTER TABLE cotation_assureur DROP FOREIGN KEY FK_B3BAD83780F7E20A');
        $this->addSql('DROP TABLE cotation_assureur');
        $this->addSql('ALTER TABLE cotation ADD assureur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA94480F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id)');
        $this->addSql('CREATE INDEX IDX_996DA94480F7E20A ON cotation (assureur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cotation_assureur (cotation_id INT NOT NULL, assureur_id INT NOT NULL, INDEX IDX_B3BAD83780F7E20A (assureur_id), INDEX IDX_B3BAD8375D14FAF0 (cotation_id), PRIMARY KEY(cotation_id, assureur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE cotation_assureur ADD CONSTRAINT FK_B3BAD8375D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cotation_assureur ADD CONSTRAINT FK_B3BAD83780F7E20A FOREIGN KEY (assureur_id) REFERENCES assureur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA94480F7E20A');
        $this->addSql('DROP INDEX IDX_996DA94480F7E20A ON cotation');
        $this->addSql('ALTER TABLE cotation DROP assureur_id');
    }
}
