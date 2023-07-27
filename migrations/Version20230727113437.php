<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727113437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sinistre_victime DROP FOREIGN KEY FK_A519E73B216966DF');
        $this->addSql('ALTER TABLE sinistre_victime DROP FOREIGN KEY FK_A519E73B75FF0F4B');
        $this->addSql('DROP TABLE sinistre_victime');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sinistre_victime (sinistre_id INT NOT NULL, victime_id INT NOT NULL, INDEX IDX_A519E73B75FF0F4B (victime_id), INDEX IDX_A519E73B216966DF (sinistre_id), PRIMARY KEY(sinistre_id, victime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sinistre_victime ADD CONSTRAINT FK_A519E73B216966DF FOREIGN KEY (sinistre_id) REFERENCES sinistre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sinistre_victime ADD CONSTRAINT FK_A519E73B75FF0F4B FOREIGN KEY (victime_id) REFERENCES victime (id) ON DELETE CASCADE');
    }
}
