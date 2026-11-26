<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'challenges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ChallengeCategory $category = null;

    /**
     * @var Collection<int, Evaluation>
     */
    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'challenge')]
    private Collection $evaluations;

    #[ORM\Column]
    private ?int $targetValue = null;

    #[ORM\Column(length: 255)]
    private ?string $unit = null;

    /** Pour une course chronométrée, le meilleur résultat est le plus petit (le temps le plus court). */
    #[ORM\Column(options: ['default' => false])]
    private bool $lowerIsBetter = false;

    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
        $this->targetValue = 0;
        $this->unit = "";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?ChallengeCategory
    {
        return $this->category;
    }

    public function setCategory(?ChallengeCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): static
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setChallenge($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getChallenge() === $this) {
                $evaluation->setChallenge(null);
            }
        }

        return $this;
    }

    public function getTargetValue(): ?int
    {
        return $this->targetValue;
    }

    public function setTargetValue(int $targetValue): static
    {
        $this->targetValue = $targetValue;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function isLowerBetter(): bool
    {
        return $this->lowerIsBetter;
    }

    public function setLowerIsBetter(bool $lowerIsBetter): static
    {
        $this->lowerIsBetter = $lowerIsBetter;

        return $this;
    }

    /** Le résultat $a est-il meilleur que $b pour ce défi ? */
    public function isBetter(float $a, float $b): bool
    {
        return $this->lowerIsBetter ? $a < $b : $a > $b;
    }

    /** L'objectif est-il atteint avec ce résultat ? */
    public function isReachedBy(float $value): bool
    {
        return $this->lowerIsBetter ? $value <= $this->targetValue : $value >= $this->targetValue;
    }
}
