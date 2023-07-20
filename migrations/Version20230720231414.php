<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230720231414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA3288790B');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BAD249A887');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BAC34065BC');
        $this->addSql('DROP INDEX IDX_A62034BAD249A887 ON action_crm');
        $this->addSql('DROP INDEX IDX_A62034BA3288790B ON action_crm');
        $this->addSql('DROP INDEX IDX_A62034BAC34065BC ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP piste_id, DROP feedback_id, DROP attributed_to_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm ADD piste_id INT DEFAULT NULL, ADD feedback_id INT DEFAULT NULL, ADD attributed_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA3288790B FOREIGN KEY (attributed_to_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BAD249A887 FOREIGN KEY (feedback_id) REFERENCES feedback_crm (id)');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BAC34065BC FOREIGN KEY (piste_id) REFERENCES piste (id)');
        $this->addSql('CREATE INDEX IDX_A62034BAD249A887 ON action_crm (feedback_id)');
        $this->addSql('CREATE INDEX IDX_A62034BA3288790B ON action_crm (attributed_to_id)');
        $this->addSql('CREATE INDEX IDX_A62034BAC34065BC ON action_crm (piste_id)');
    }
}
