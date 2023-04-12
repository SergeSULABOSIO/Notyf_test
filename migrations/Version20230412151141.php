<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230412151141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE police_piste (police_id INT NOT NULL, piste_id INT NOT NULL, INDEX IDX_425A2F3637E60BE1 (police_id), INDEX IDX_425A2F36C34065BC (piste_id), PRIMARY KEY(police_id, piste_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE police_piste ADD CONSTRAINT FK_425A2F3637E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE police_piste ADD CONSTRAINT FK_425A2F36C34065BC FOREIGN KEY (piste_id) REFERENCES piste (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police_piste DROP FOREIGN KEY FK_425A2F3637E60BE1');
        $this->addSql('ALTER TABLE police_piste DROP FOREIGN KEY FK_425A2F36C34065BC');
        $this->addSql('DROP TABLE police_piste');
    }
}
