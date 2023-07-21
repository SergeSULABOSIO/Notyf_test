<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230721145148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_crm ADD action_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback_crm ADD CONSTRAINT FK_83009D429D32F035 FOREIGN KEY (action_id) REFERENCES action_crm (id)');
        $this->addSql('CREATE INDEX IDX_83009D429D32F035 ON feedback_crm (action_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_crm DROP FOREIGN KEY FK_83009D429D32F035');
        $this->addSql('DROP INDEX IDX_83009D429D32F035 ON feedback_crm');
        $this->addSql('ALTER TABLE feedback_crm DROP action_id');
    }
}
