<?php

namespace App\Tests\Unit;

use App\Entity\Challenge;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Meilleur résultat et objectif atteint, pour un défi classique (plus c'est grand, mieux c'est)
 * et pour une course chronométrée (plus c'est petit, mieux c'est).
 */
class ChallengeTest extends TestCase
{
    #[DataProvider('betterProvider')]
    public function testIsBetter(bool $lowerIsBetter, float $a, float $b, bool $expected): void
    {
        $challenge = (new Challenge())->setLowerIsBetter($lowerIsBetter);

        $this->assertSame($expected, $challenge->isBetter($a, $b));
    }

    public static function betterProvider(): array
    {
        return [
            '25 pompes battent 20 pompes' => [false, 25, 20, true],
            '20 pompes ne battent pas 25' => [false, 20, 25, false],
            'égalité : pas meilleur' => [false, 20, 20, false],
            '30 min battent 35 min en course' => [true, 1800, 2100, true],
            '35 min ne battent pas 30 min' => [true, 2100, 1800, false],
        ];
    }

    #[DataProvider('reachedProvider')]
    public function testIsReachedBy(bool $lowerIsBetter, int $target, float $value, bool $expected): void
    {
        $challenge = (new Challenge())
            ->setTargetValue($target)
            ->setLowerIsBetter($lowerIsBetter);

        $this->assertSame($expected, $challenge->isReachedBy($value));
    }

    public static function reachedProvider(): array
    {
        return [
            'objectif pile atteint' => [false, 30, 30, true],
            'objectif dépassé' => [false, 30, 42, true],
            'objectif pas atteint' => [false, 30, 29, false],
            'course : temps égal à l\'objectif' => [true, 1800, 1800, true],
            'course : plus rapide que l\'objectif' => [true, 1800, 1700, true],
            'course : trop lent' => [true, 1800, 1801, false],
        ];
    }
}
