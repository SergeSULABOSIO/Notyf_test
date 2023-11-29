<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129141633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_crm ADD action_crm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback_crm ADD CONSTRAINT FK_83009D422ECC6D6F FOREIGN KEY (action_crm_id) REFERENCES action_crm (id)');
        $this->addSql('CREATE INDEX IDX_83009D422ECC6D6F ON feedback_crm (action_crm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_crm DROP FOREIGN KEY FK_83009D422ECC6D6F');
        $this->addSql('DROP INDEX IDX_83009D422ECC6D6F ON feedback_crm');
        $this->addSql('ALTER TABLE feedback_crm DROP action_crm_id');
    }
}
