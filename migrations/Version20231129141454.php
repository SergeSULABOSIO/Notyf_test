<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129141454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece ADD action_crm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_piece ADD CONSTRAINT FK_2B2366752ECC6D6F FOREIGN KEY (action_crm_id) REFERENCES action_crm (id)');
        $this->addSql('CREATE INDEX IDX_2B2366752ECC6D6F ON doc_piece (action_crm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doc_piece DROP FOREIGN KEY FK_2B2366752ECC6D6F');
        $this->addSql('DROP INDEX IDX_2B2366752ECC6D6F ON doc_piece');
        $this->addSql('ALTER TABLE doc_piece DROP action_crm_id');
    }
}
