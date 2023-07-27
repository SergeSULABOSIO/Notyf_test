<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727151242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doc_piece_paiement_commission (doc_piece_id INT NOT NULL, paiement_commission_id INT NOT NULL, INDEX IDX_C8271FE2A5BBD2F3 (doc_piece_id), INDEX IDX_C8271FE2654CF4FC (paiement_commission_id), PRIMARY KEY(doc_piece_id, paiement_commission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doc_piece_paiement_commission ADD CONSTRAINT FK_C8271FE2A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_paiement_commission ADD CONSTRAINT FK_C8271FE2654CF4FC FOREIGN KEY (paiement_commission_id) REFERENCES paiement_commission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece DROP FOREIGN KEY FK_48018DC0654CF4FC');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece DROP FOREIGN KEY FK_48018DC0A5BBD2F3');
        $this->addSql('DROP TABLE paiement_commission_doc_piece');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paiement_commission_doc_piece (paiement_commission_id INT NOT NULL, doc_piece_id INT NOT NULL, INDEX IDX_48018DC0654CF4FC (paiement_commission_id), INDEX IDX_48018DC0A5BBD2F3 (doc_piece_id), PRIMARY KEY(paiement_commission_id, doc_piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece ADD CONSTRAINT FK_48018DC0654CF4FC FOREIGN KEY (paiement_commission_id) REFERENCES paiement_commission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_commission_doc_piece ADD CONSTRAINT FK_48018DC0A5BBD2F3 FOREIGN KEY (doc_piece_id) REFERENCES doc_piece (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc_piece_paiement_commission DROP FOREIGN KEY FK_C8271FE2A5BBD2F3');
        $this->addSql('ALTER TABLE doc_piece_paiement_commission DROP FOREIGN KEY FK_C8271FE2654CF4FC');
        $this->addSql('DROP TABLE doc_piece_paiement_commission');
    }
}
