<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231124154348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police DROP dateoperation, DROP dateemission, DROP dateeffet, DROP dateexpiration');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police ADD dateoperation DATE NOT NULL, ADD dateemission DATE NOT NULL, ADD dateeffet DATE NOT NULL, ADD dateexpiration DATE NOT NULL');
    }
}
