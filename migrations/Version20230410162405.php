<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410162405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cotation_doc_piece (cotation_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_8857D2805D14FAF0 (cotation_id), INDEX IDX_8857D280A5BBD2F3 (doc_piece_id), PRIMARY KEY(cotation_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cotation_doc_piece ADD CONSTRAINT FK_8857D2805D14FAF0 FOREIGN KEY (cotation_id) REFERENCES cotation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cotation_doc_piece ADD CONSTRAINT FK_8857D280A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation_doc_piece DROP FOREIGN KEY FK_8857D2805D14FAF0');
        $this->addSql('ALTER TABLE cotation_doc_piece DROP FOREIGN KEY FK_8857D280A5BBD2F3');
        $this->addSql('DROP TABLE cotation_doc_piece');
    }
}
