<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109093023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation ADD id_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A57579F37AE5 FOREIGN KEY (id_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1323A57579F37AE5 ON evaluation (id_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE evaluation DROP CONSTRAINT FK_1323A57579F37AE5');
        $this->addSql('DROP INDEX IDX_1323A57579F37AE5');
        $this->addSql('ALTER TABLE evaluation DROP id_user_id');
    }
}
