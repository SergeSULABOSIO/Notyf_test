<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802151017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police ADD paidcommission DOUBLE PRECISION DEFAULT NULL, ADD paidretrocommission DOUBLE PRECISION DEFAULT NULL, ADD paidtaxecourtier DOUBLE PRECISION DEFAULT NULL, ADD paidtaxeassureur DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE police DROP paidcommission, DROP paidretrocommission, DROP paidtaxecourtier, DROP paidtaxeassureur');
    }
}
