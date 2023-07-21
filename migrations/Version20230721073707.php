<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230721073707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste DROP FOREIGN KEY FK_59E250774A8CA2AD');
        $this->addSql('DROP INDEX IDX_59E250774A8CA2AD ON piste');
        $this->addSql('ALTER TABLE piste DROP etape_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piste ADD etape_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE piste ADD CONSTRAINT FK_59E250774A8CA2AD FOREIGN KEY (etape_id) REFERENCES etape_crm (id)');
        $this->addSql('CREATE INDEX IDX_59E250774A8CA2AD ON piste (etape_id)');
    }
}
