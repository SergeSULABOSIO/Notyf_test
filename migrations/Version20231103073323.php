<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103073323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police_facture DROP FOREIGN KEY FK_D75E6D4137E60BE1');
        $this->addSql('ALTER TABLE police_facture DROP FOREIGN KEY FK_D75E6D417F2DEE08');
        $this->addSql('DROP TABLE police_facture');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE police_facture (police_id INT NOT NULL, facture_id INT NOT NULL, INDEX IDX_D75E6D4137E60BE1 (police_id), INDEX IDX_D75E6D417F2DEE08 (facture_id), PRIMARY KEY(police_id, facture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE police_facture ADD CONSTRAINT FK_D75E6D4137E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_facture ADD CONSTRAINT FK_D75E6D417F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
    }
}
