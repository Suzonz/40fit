<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Évaluations : la colonne de l'utilisateur suit le nouveau nom du champ (associatedUser),
 * chaque résultat a une date, et un résultat a toujours un défi, un utilisateur et une valeur.
 * La colonne est renommée (et pas supprimée puis recréée) pour garder les résultats existants.
 */
final class Version20261125204530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Évaluation : id_user_id renommé en associated_user_id, ajout de created_at, champs obligatoires ; défi : lower_is_better';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evaluation DROP CONSTRAINT fk_1323a57579f37ae5');
        $this->addSql('DROP INDEX idx_1323a57579f37ae5');
        // Les résultats incomplets (sans utilisateur, sans défi ou sans valeur) ne peuvent pas être gardés
        $this->addSql('DELETE FROM evaluation WHERE id_user_id IS NULL OR challenge_id IS NULL OR value IS NULL');
        $this->addSql('ALTER TABLE evaluation RENAME COLUMN id_user_id TO associated_user_id');
        $this->addSql('ALTER TABLE evaluation ALTER associated_user_id SET NOT NULL');
        // Les résultats déjà enregistrés prennent la date de la migration
        $this->addSql('ALTER TABLE evaluation ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW() NOT NULL');
        $this->addSql('ALTER TABLE evaluation ALTER created_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN evaluation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE evaluation ALTER challenge_id SET NOT NULL');
        $this->addSql('ALTER TABLE evaluation ALTER value SET NOT NULL');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575BC272CD1 FOREIGN KEY (associated_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1323A575BC272CD1 ON evaluation (associated_user_id)');

        // Défis chronométrés : le meilleur résultat est le temps le plus court
        $this->addSql('ALTER TABLE challenge ADD lower_is_better BOOLEAN DEFAULT false NOT NULL');
        $this->addSql("UPDATE challenge SET lower_is_better = true WHERE title IN ('Course 1500m', 'Course 5km')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE challenge DROP lower_is_better');
        $this->addSql('ALTER TABLE evaluation DROP CONSTRAINT FK_1323A575BC272CD1');
        $this->addSql('DROP INDEX IDX_1323A575BC272CD1');
        $this->addSql('ALTER TABLE evaluation DROP created_at');
        $this->addSql('ALTER TABLE evaluation ALTER challenge_id DROP NOT NULL');
        $this->addSql('ALTER TABLE evaluation ALTER value DROP NOT NULL');
        $this->addSql('ALTER TABLE evaluation RENAME COLUMN associated_user_id TO id_user_id');
        $this->addSql('ALTER TABLE evaluation ALTER id_user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT fk_1323a57579f37ae5 FOREIGN KEY (id_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1323a57579f37ae5 ON evaluation (id_user_id)');
    }
}
