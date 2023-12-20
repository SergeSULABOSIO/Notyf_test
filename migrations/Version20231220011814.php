<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220011814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944A2561908');
        $this->addSql('DROP INDEX IDX_996DA944A2561908 ON cotation');
        $this->addSql('ALTER TABLE cotation ADD assistant_id INT DEFAULT NULL, CHANGE assistante_id gestionnaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA9446885AC1B FOREIGN KEY (gestionnaire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944E05387EF FOREIGN KEY (assistant_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_996DA9446885AC1B ON cotation (gestionnaire_id)');
        $this->addSql('CREATE INDEX IDX_996DA944E05387EF ON cotation (assistant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA9446885AC1B');
        $this->addSql('ALTER TABLE cotation DROP FOREIGN KEY FK_996DA944E05387EF');
        $this->addSql('DROP INDEX IDX_996DA9446885AC1B ON cotation');
        $this->addSql('DROP INDEX IDX_996DA944E05387EF ON cotation');
        $this->addSql('ALTER TABLE cotation ADD assistante_id INT DEFAULT NULL, DROP gestionnaire_id, DROP assistant_id');
        $this->addSql('ALTER TABLE cotation ADD CONSTRAINT FK_996DA944A2561908 FOREIGN KEY (assistante_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_996DA944A2561908 ON cotation (assistante_id)');
    }
}
