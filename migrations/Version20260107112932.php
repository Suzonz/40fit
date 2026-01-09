<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107112932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE challenge_category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE challenge ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE challenge ADD description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE challenge DROP category_name');
        $this->addSql('ALTER TABLE challenge DROP decription');
        $this->addSql('ALTER TABLE challenge DROP time_goal');
        $this->addSql('ALTER TABLE challenge DROP repetition_goal');
        $this->addSql('ALTER TABLE challenge DROP distance_goal');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D709895112469DE2 FOREIGN KEY (category_id) REFERENCES challenge_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D709895112469DE2 ON challenge (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE challenge DROP CONSTRAINT FK_D709895112469DE2');
        $this->addSql('DROP TABLE challenge_category');
        $this->addSql('DROP INDEX IDX_D709895112469DE2');
        $this->addSql('ALTER TABLE challenge ADD decription VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE challenge ADD time_goal TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE challenge ADD repetition_goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE challenge ADD distance_goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE challenge DROP category_id');
        $this->addSql('ALTER TABLE challenge RENAME COLUMN description TO category_name');
    }
}
