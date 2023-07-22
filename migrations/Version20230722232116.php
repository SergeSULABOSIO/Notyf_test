<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230722232116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece ADD cotation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B2366755D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id)');
        $this->addSql('CREATE INDEX IDX_2B2366755D14FAF0 ON doc_piece (cotation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B2366755D14FAF0');
        $this->addSql('DROP INDEX IDX_2B2366755D14FAF0 ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP cotation_id');
    }
}
