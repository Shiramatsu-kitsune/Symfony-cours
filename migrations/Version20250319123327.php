<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319123327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD trigger_migration VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE categorie ADD trigger_migration VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD trigger_migration VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE poste ADD trigger_migration VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD trigger_migration VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP trigger_migration');
        $this->addSql('ALTER TABLE categorie DROP trigger_migration');
        $this->addSql('ALTER TABLE article DROP trigger_migration');
        $this->addSql('ALTER TABLE poste DROP trigger_migration');
        $this->addSql('ALTER TABLE `user` DROP trigger_migration');
    }
}
