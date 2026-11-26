<?php

namespace App\Tests\Unit;

use App\Entity\Challenge;
use App\Entity\Evaluation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Le pourcentage de l'objectif atteint par un résultat (Evaluation::getProgress).
 */
class EvaluationProgressTest extends TestCase
{
    #[DataProvider('progressProvider')]
    public function testProgress(int $target, bool $lowerIsBetter, ?float $value, int $expected): void
    {
        $challenge = (new Challenge())
            ->setTargetValue($target)
            ->setLowerIsBetter($lowerIsBetter);
        $evaluation = (new Evaluation())
            ->setChallenge($challenge)
            ->setValue($value);

        $this->assertSame($expected, $evaluation->getProgress());
    }

    public static function progressProvider(): array
    {
        return [
            'moitié de l\'objectif' => [30, false, 15, 50],
            'objectif atteint' => [30, false, 30, 100],
            'objectif dépassé : plafonné à 100' => [30, false, 60, 100],
            'presque atteint : 99 et pas 100' => [1000, false, 999, 99],
            'résultat à zéro' => [30, false, 0, 0],
            'course : 40 min pour 30 min visées' => [1800, true, 2400, 75],
            'course : plus rapide que l\'objectif' => [1800, true, 1500, 100],
            'course : temps à zéro' => [1800, true, 0, 100],
            'objectif à zéro : pas de division par zéro' => [0, false, 15, 0],
            'pas encore de résultat' => [30, false, null, 0],
        ];
    }

    public function testProgressWithoutChallenge(): void
    {
        $this->assertSame(0, (new Evaluation())->setValue(10)->getProgress());
    }
}
