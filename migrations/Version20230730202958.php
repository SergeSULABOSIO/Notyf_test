<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230730202958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE automobile_police DROP FOREIGN KEY FK_5AD6733E37E60BE1');
        $this->addSql('ALTER TABLE automobile_police DROP FOREIGN KEY FK_5AD6733E50E09BD4');
        $this->addSql('DROP TABLE automobile_police');
        $this->addSql('ALTER TABLE automobile ADD police_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE automobile ADD CONSTRAINT FK_BFCEA08737E60BE1 FOREIGN KEY (police_id) REFERENCES police (id)');
        $this->addSql('CREATE INDEX IDX_BFCEA08737E60BE1 ON automobile (police_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE automobile_police (automobile_id INT NOT NULL, police_id INT NOT NULL, INDEX IDX_5AD6733E50E09BD4 (automobile_id), INDEX IDX_5AD6733E37E60BE1 (police_id), PRIMARY KEY(automobile_id, police_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE automobile_police ADD CONSTRAINT FK_5AD6733E37E60BE1 FOREIGN KEY (police_id) REFERENCES police (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE automobile_police ADD CONSTRAINT FK_5AD6733E50E09BD4 FOREIGN KEY (automobile_id) REFERENCES automobile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE automobile DROP FOREIGN KEY FK_BFCEA08737E60BE1');
        $this->addSql('DROP INDEX IDX_BFCEA08737E60BE1 ON automobile');
        $this->addSql('ALTER TABLE automobile DROP police_id');
    }
}
