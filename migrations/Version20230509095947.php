<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230509095947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action_crm_utilisateur DROP FOREIGN KEY FK_9D28A0E02ECC6D6F');
        $this->addSql('ALTER TABLE action_crm_utilisateur DROP FOREIGN KEY FK_9D28A0E0FB88E14F');
        $this->addSql('DROP TABLE action_crm_utilisateur');
        $this->addSql('ALTER TABLE action_crm ADD attributed_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE action_crm ADD CONSTRAINT FK_A62034BA3288790B FOREIGN KEY (attributed_to_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_A62034BA3288790B ON action_crm (attributed_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action_crm_utilisateur (action_crm_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_9D28A0E02ECC6D6F (action_crm_id), INDEX IDX_9D28A0E0FB88E14F (utilisateur_id), PRIMARY KEY(action_crm_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE action_crm_utilisateur ADD CONSTRAINT FK_9D28A0E02ECC6D6F FOREIGN KEY (action_crm_id) REFERENCES action_crm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_crm_utilisateur ADD CONSTRAINT FK_9D28A0E0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_crm DROP FOREIGN KEY FK_A62034BA3288790B');
        $this->addSql('DROP INDEX IDX_A62034BA3288790B ON action_crm');
        $this->addSql('ALTER TABLE action_crm DROP attributed_to_id');
    }
}
