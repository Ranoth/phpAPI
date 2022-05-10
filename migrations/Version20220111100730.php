<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111100730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auteur DROP FOREIGN KEY FK_55AB1403256915B');
        $this->addSql('DROP INDEX IDX_55AB1403256915B ON auteur');
        $this->addSql('ALTER TABLE auteur CHANGE relation_id nationalite_id INT NOT NULL');
        $this->addSql('ALTER TABLE auteur ADD CONSTRAINT FK_55AB1401B063272 FOREIGN KEY (nationalite_id) REFERENCES nationalite (id)');
        $this->addSql('CREATE INDEX IDX_55AB1401B063272 ON auteur (nationalite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auteur DROP FOREIGN KEY FK_55AB1401B063272');
        $this->addSql('DROP INDEX IDX_55AB1401B063272 ON auteur');
        $this->addSql('ALTER TABLE auteur CHANGE nationalite_id relation_id INT NOT NULL');
        $this->addSql('ALTER TABLE auteur ADD CONSTRAINT FK_55AB1403256915B FOREIGN KEY (relation_id) REFERENCES nationalite (id)');
        $this->addSql('CREATE INDEX IDX_55AB1403256915B ON auteur (relation_id)');
    }
}
