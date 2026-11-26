<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Un résultat d'un utilisateur à un défi (ex. 25 pompes), à une date donnée :
 * plusieurs résultats au même défi montrent la progression dans le temps.
 */
#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Challenge $challenge = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $associatedUser = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Indiquez votre résultat.')]
    #[Assert\PositiveOrZero(message: 'Le résultat ne peut pas être négatif.')]
    private ?float $value = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    // Accepte null : un champ laissé vide est refusé par la validation (message), pas par une erreur PHP
    public function setValue(?float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): static
    {
        $this->challenge = $challenge;

        return $this;
    }

    public function getAssociatedUser(): ?User
    {
        return $this->associatedUser;
    }

    public function setAssociatedUser(?User $associatedUser): static
    {
        $this->associatedUser = $associatedUser;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Part de l'objectif du défi atteinte (en %, plafonnée à 100).
     * Pour une course chronométrée, finir en 40 min au lieu de 30 donne 75 %.
     */
    public function getProgress(): int
    {
        $target = $this->challenge?->getTargetValue();
        if (!$target || $this->value === null) {
            return 0;
        }
        if ($this->challenge->isReachedBy($this->value)) {
            return 100;
        }
        $ratio = $this->challenge->isLowerBetter()
            ? ($this->value > 0 ? $target / $this->value : 0)
            : $this->value / $target;

        return (int) min(99, round($ratio * 100));
    }
}
