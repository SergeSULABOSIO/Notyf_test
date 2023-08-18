<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230818130820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE piste_contact (piste_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_D3E56148C34065BC (piste_id), INDEX IDX_D3E56148E7A1254A (contact_id), PRIMARY KEY(piste_id, contact_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE piste_contact ADD CONSTRAINT FK_D3E56148C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piste_contact ADD CONSTRAINT FK_D3E56148E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste_contact DROP FOREIGN KEY FK_D3E56148C34065BC');
        $this->addSql('ALTER TABLE piste_contact DROP FOREIGN KEY FK_D3E56148E7A1254A');
        $this->addSql('DROP TABLE piste_contact');
    }
}
