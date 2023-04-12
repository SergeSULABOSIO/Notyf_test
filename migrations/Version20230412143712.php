<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230412143712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE piste_action_crm (piste_id INT NOT NULL, action_crm_id INT NOT NULL, INDEX IDX_F5CF447AC34065BC (piste_id), INDEX IDX_F5CF447A2ECC6D6F (action_crm_id), PRIMARY KEY(piste_id, action_crm_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE piste_action_crm ADD CONSTRAINT FK_F5CF447AC34065BC FOREIGN KEY (piste_id) REFERENCES piste (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piste_action_crm ADD CONSTRAINT FK_F5CF447A2ECC6D6F FOREIGN KEY (action_crm_id) REFERENCES action_crm (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste_action_crm DROP FOREIGN KEY FK_F5CF447AC34065BC');
        $this->addSql('ALTER TABLE piste_action_crm DROP FOREIGN KEY FK_F5CF447A2ECC6D6F');
        $this->addSql('DROP TABLE piste_action_crm');
    }
}
