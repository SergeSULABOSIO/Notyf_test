<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230909144802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture_compte_bancaire (facture_id INT NOT NULL, compte_bancaire_id INT NOT NULL, INDEX IDX_98CB295B7F2DEE08 (facture_id), INDEX IDX_98CB295BAF1E371E (compte_bancaire_id), PRIMARY KEY(facture_id, compte_bancaire_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE facture_compte_bancaire ADD CONSTRAINT FK_98CB295B7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facture_compte_bancaire ADD CONSTRAINT FK_98CB295BAF1E371E FOREIGN KEY (compte_bancaire_id) REFERENCES compte_bancaire (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture_compte_bancaire DROP FOREIGN KEY FK_98CB295B7F2DEE08');
        $this->addSql('ALTER TABLE facture_compte_bancaire DROP FOREIGN KEY FK_98CB295BAF1E371E');
        $this->addSql('DROP TABLE facture_compte_bancaire');
    }
}
